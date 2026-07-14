import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/auth_providers.dart';
import '../book_providers.dart';

class BooksListScreen extends ConsumerStatefulWidget {
  const BooksListScreen({super.key});

  @override
  ConsumerState<BooksListScreen> createState() => _BooksListScreenState();
}

class _BooksListScreenState extends ConsumerState<BooksListScreen> {
  final _titulo = TextEditingController();
  final _indice = TextEditingController();

  @override
  void dispose() {
    _titulo.dispose();
    _indice.dispose();
    super.dispose();
  }

  void _aplicarFiltros() {
    ref.read(bookFiltersProvider.notifier).state = BookFilters(
      titulo: _titulo.text.trim(),
      tituloDoIndice: _indice.text.trim(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final books = ref.watch(booksListProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Livros'),
        actions: [
          IconButton(
            tooltip: 'Sair',
            icon: const Icon(Icons.logout),
            onPressed: () => ref.read(authControllerProvider.notifier).logout(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/books/new'),
        icon: const Icon(Icons.add),
        label: const Text('Novo livro'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                TextField(
                  controller: _titulo,
                  decoration: const InputDecoration(
                    labelText: 'Filtrar por titulo',
                    prefixIcon: Icon(Icons.search),
                  ),
                  onSubmitted: (_) => _aplicarFiltros(),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _indice,
                  decoration: const InputDecoration(
                    labelText: 'Filtrar por titulo do indice',
                    prefixIcon: Icon(Icons.account_tree),
                  ),
                  onSubmitted: (_) => _aplicarFiltros(),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: _aplicarFiltros,
                        icon: const Icon(Icons.filter_alt),
                        label: const Text('Aplicar filtros'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    OutlinedButton(
                      onPressed: () {
                        _titulo.clear();
                        _indice.clear();
                        _aplicarFiltros();
                      },
                      child: const Text('Limpar'),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          Expanded(
            child: books.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Erro: $e')),
              data: (list) {
                if (list.isEmpty) {
                  return const Center(child: Text('Nenhum livro encontrado.'));
                }
                return RefreshIndicator(
                  onRefresh: () async => ref.refresh(booksListProvider.future),
                  child: ListView.separated(
                    itemCount: list.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (_, i) {
                      final book = list[i];
                      return ListTile(
                        title: Text(book.titulo),
                        subtitle: Text(
                          '${book.numeroPaginas} paginas'
                          '${book.usuarioPublicador != null ? '  -  ${book.usuarioPublicador!.nome}' : ''}',
                        ),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () => context.push('/books/${book.id}'),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
