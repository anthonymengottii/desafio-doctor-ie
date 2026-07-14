import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../book_providers.dart';
import '../models/book_index.dart';

/// No editavel da arvore de indices (usado apenas na tela de formulario).
class _EditNode {
  _EditNode({required this.titulo, required this.pagina, List<_EditNode>? filhos})
      : filhos = filhos ?? [];

  String titulo;
  int pagina;
  List<_EditNode> filhos;

  factory _EditNode.fromModel(BookIndex i) => _EditNode(
        titulo: i.titulo,
        pagina: i.pagina,
        filhos: i.subindices.map(_EditNode.fromModel).toList(),
      );

  BookIndex toModel() => BookIndex(
        titulo: titulo,
        pagina: pagina,
        subindices: filhos.map((f) => f.toModel()).toList(),
      );
}

class BookFormScreen extends ConsumerStatefulWidget {
  const BookFormScreen({super.key, this.bookId});

  final int? bookId;

  bool get isEdit => bookId != null;

  @override
  ConsumerState<BookFormScreen> createState() => _BookFormScreenState();
}

class _BookFormScreenState extends ConsumerState<BookFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titulo = TextEditingController();
  final _paginas = TextEditingController();
  final List<_EditNode> _raizes = [];

  bool _carregando = false;
  bool _salvando = false;
  bool _seeded = false;

  @override
  void initState() {
    super.initState();
    if (widget.isEdit) _carregarLivro();
  }

  @override
  void dispose() {
    _titulo.dispose();
    _paginas.dispose();
    super.dispose();
  }

  Future<void> _carregarLivro() async {
    setState(() => _carregando = true);
    try {
      final book = await ref.read(bookRepositoryProvider).show(widget.bookId!);
      if (!_seeded) {
        _titulo.text = book.titulo;
        _paginas.text = book.numeroPaginas.toString();
        _raizes
          ..clear()
          ..addAll(book.indices.map(_EditNode.fromModel));
        _seeded = true;
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _carregando = false);
    }
  }

  Future<void> _editarNo(_EditNode? no, {List<_EditNode>? destino}) async {
    final tituloCtrl = TextEditingController(text: no?.titulo ?? '');
    final paginaCtrl =
        TextEditingController(text: no != null ? no.pagina.toString() : '');

    final salvou = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(no == null ? 'Novo indice' : 'Editar indice'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: tituloCtrl,
              decoration: const InputDecoration(labelText: 'Titulo'),
              autofocus: true,
            ),
            TextField(
              controller: paginaCtrl,
              decoration: const InputDecoration(labelText: 'Pagina'),
              keyboardType: TextInputType.number,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Salvar')),
        ],
      ),
    );

    if (salvou != true) return;
    final titulo = tituloCtrl.text.trim();
    final pagina = int.tryParse(paginaCtrl.text.trim()) ?? 0;
    if (titulo.isEmpty || pagina < 1) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Titulo obrigatorio e pagina >= 1')),
        );
      }
      return;
    }

    setState(() {
      if (no == null) {
        destino!.add(_EditNode(titulo: titulo, pagina: pagina));
      } else {
        no.titulo = titulo;
        no.pagina = pagina;
      }
    });
  }

  void _remover(List<_EditNode> lista, _EditNode no) {
    setState(() => lista.remove(no));
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _salvando = true);

    final repo = ref.read(bookRepositoryProvider);
    final titulo = _titulo.text.trim();
    final paginas = int.parse(_paginas.text.trim());
    final indices = _raizes.map((n) => n.toModel()).toList();

    try {
      if (widget.isEdit) {
        await repo.update(widget.bookId!,
            titulo: titulo, numeroPaginas: paginas, indices: indices);
        ref.invalidate(bookDetailProvider(widget.bookId!));
      } else {
        await repo.create(titulo: titulo, numeroPaginas: paginas, indices: indices);
      }
      ref.invalidate(booksListProvider);
      if (mounted) context.go('/books');
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _salvando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_carregando) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(title: Text(widget.isEdit ? 'Editar livro' : 'Novo livro')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _titulo,
              decoration: const InputDecoration(labelText: 'Titulo'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Informe o titulo' : null,
            ),
            TextFormField(
              controller: _paginas,
              decoration: const InputDecoration(labelText: 'Numero de paginas'),
              keyboardType: TextInputType.number,
              validator: (v) {
                final n = int.tryParse(v?.trim() ?? '');
                return (n == null || n < 1) ? 'Numero de paginas invalido' : null;
              },
            ),
            const Divider(height: 32),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Indices', style: Theme.of(context).textTheme.titleMedium),
                TextButton.icon(
                  onPressed: () => _editarNo(null, destino: _raizes),
                  icon: const Icon(Icons.add),
                  label: const Text('Adicionar raiz'),
                ),
              ],
            ),
            if (_raizes.isEmpty)
              const Padding(
                padding: EdgeInsets.all(8),
                child: Text('Nenhum indice. Adicione ao menos um se desejar.'),
              ),
            for (final no in _raizes) _NoEditor(node: no, lista: _raizes, screen: this),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _salvando ? null : _salvar,
              child: _salvando
                  ? const SizedBox(
                      height: 20, width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Salvar'),
            ),
          ],
        ),
      ),
    );
  }
}

/// Renderiza recursivamente um no editavel e seus filhos.
class _NoEditor extends StatelessWidget {
  const _NoEditor({required this.node, required this.lista, required this.screen});

  final _EditNode node;
  final List<_EditNode> lista;
  final _BookFormScreenState screen;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 12, top: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Card(
            margin: EdgeInsets.zero,
            child: ListTile(
              dense: true,
              title: Text(node.titulo),
              subtitle: Text('pagina ${node.pagina}'),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    tooltip: 'Adicionar subindice',
                    icon: const Icon(Icons.subdirectory_arrow_right),
                    onPressed: () => screen._editarNo(null, destino: node.filhos),
                  ),
                  IconButton(
                    tooltip: 'Editar',
                    icon: const Icon(Icons.edit),
                    onPressed: () => screen._editarNo(node),
                  ),
                  IconButton(
                    tooltip: 'Remover',
                    icon: const Icon(Icons.delete),
                    onPressed: () => screen._remover(lista, node),
                  ),
                ],
              ),
            ),
          ),
          for (final filho in node.filhos)
            _NoEditor(node: filho, lista: node.filhos, screen: screen),
        ],
      ),
    );
  }
}
