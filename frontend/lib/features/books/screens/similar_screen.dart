import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../book_providers.dart';

class SimilarScreen extends ConsumerWidget {
  const SimilarScreen({super.key, required this.bookId});

  final int bookId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final base = ref.watch(bookDetailProvider(bookId));
    final similar = ref.watch(similarBooksProvider(bookId));

    return Scaffold(
      appBar: AppBar(title: const Text('Livros similares')),
      body: Column(
        children: [
          base.maybeWhen(
            data: (b) => Padding(
              padding: const EdgeInsets.all(16),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text('Semelhantes a: "${b.titulo}"',
                    style: Theme.of(context).textTheme.titleMedium),
              ),
            ),
            orElse: () => const SizedBox.shrink(),
          ),
          const Divider(height: 1),
          Expanded(
            child: similar.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Erro: $e')),
              data: (list) {
                if (list.isEmpty) {
                  return const Center(child: Text('Nenhum livro similar encontrado.'));
                }
                return ListView.separated(
                  itemCount: list.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (_, i) {
                    final b = list[i];
                    return ListTile(
                      leading: const Icon(Icons.menu_book),
                      title: Text(b.titulo),
                      subtitle: Text('${b.numeroPaginas} paginas'),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => context.push('/books/${b.id}'),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
