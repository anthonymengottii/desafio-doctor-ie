import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../book_providers.dart';
import '../widgets/index_tree_view.dart';

class BookDetailScreen extends ConsumerWidget {
  const BookDetailScreen({super.key, required this.id});

  final int id;

  Future<void> _confirmarExclusao(BuildContext context, WidgetRef ref) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Excluir livro'),
        content: const Text('Isso remove o livro e todos os seus indices. Confirmar?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Excluir')),
        ],
      ),
    );
    if (ok != true) return;

    try {
      await ref.read(bookRepositoryProvider).delete(id);
      ref.invalidate(booksListProvider);
      if (context.mounted) context.go('/books');
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final book = ref.watch(bookDetailProvider(id));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detalhes do livro'),
        actions: [
          IconButton(
            tooltip: 'Similares',
            icon: const Icon(Icons.auto_awesome),
            onPressed: () => context.push('/books/$id/similar'),
          ),
          IconButton(
            tooltip: 'Editar',
            icon: const Icon(Icons.edit),
            onPressed: () => context.push('/books/$id/edit'),
          ),
          IconButton(
            tooltip: 'Excluir',
            icon: const Icon(Icons.delete),
            onPressed: () => _confirmarExclusao(context, ref),
          ),
        ],
      ),
      body: book.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Erro: $e')),
        data: (b) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(b.titulo, style: Theme.of(context).textTheme.headlineSmall),
            const SizedBox(height: 8),
            Text('${b.numeroPaginas} paginas'),
            if (b.usuarioPublicador != null)
              Text('Publicado por ${b.usuarioPublicador!.nome}'),
            const Divider(height: 32),
            Text('Indices', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            IndexTreeView(indices: b.indices),
          ],
        ),
      ),
    );
  }
}
