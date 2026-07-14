import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers.dart';
import '../auth/auth_providers.dart';
import 'book_repository.dart';
import 'models/book.dart';

final bookRepositoryProvider = Provider<BookRepository>(
  (ref) => BookRepository(ref.watch(apiClientProvider)),
);

/// Filtros ativos na listagem de livros.
class BookFilters {
  const BookFilters({this.titulo = '', this.tituloDoIndice = ''});

  final String titulo;
  final String tituloDoIndice;

  BookFilters copyWith({String? titulo, String? tituloDoIndice}) => BookFilters(
        titulo: titulo ?? this.titulo,
        tituloDoIndice: tituloDoIndice ?? this.tituloDoIndice,
      );
}

final bookFiltersProvider = StateProvider<BookFilters>((ref) => const BookFilters());

/// Lista de livros reativa aos filtros e ao estado de autenticacao.
/// Observar o auth garante um novo fetch (com token) apos o login, evitando
/// reaproveitar um estado de erro 401 gerado antes de autenticar.
final booksListProvider = FutureProvider<List<Book>>((ref) {
  ref.watch(authControllerProvider);
  final filters = ref.watch(bookFiltersProvider);
  return ref.watch(bookRepositoryProvider).list(
        titulo: filters.titulo,
        tituloDoIndice: filters.tituloDoIndice,
      );
});

/// Detalhe de um livro por id.
final bookDetailProvider = FutureProvider.family<Book, int>((ref, id) {
  return ref.watch(bookRepositoryProvider).show(id);
});

/// Livros similares a um livro existente.
final similarBooksProvider = FutureProvider.family<List<Book>, int>((ref, id) {
  return ref.watch(bookRepositoryProvider).similarToBook(id);
});
