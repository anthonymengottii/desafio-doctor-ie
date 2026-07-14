import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers.dart';
import '../auth/auth_providers.dart';
import 'book_repository.dart';
import 'models/book.dart';
import 'models/books_page.dart';

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

/// Estado da listagem paginada: itens acumulados + controle de paginacao.
class BooksListState {
  const BooksListState({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    this.loadingMore = false,
  });

  final List<Book> items;
  final int currentPage;
  final int lastPage;
  final bool loadingMore;

  bool get hasMore => currentPage < lastPage;

  BooksListState copyWith({
    List<Book>? items,
    int? currentPage,
    int? lastPage,
    bool? loadingMore,
  }) =>
      BooksListState(
        items: items ?? this.items,
        currentPage: currentPage ?? this.currentPage,
        lastPage: lastPage ?? this.lastPage,
        loadingMore: loadingMore ?? this.loadingMore,
      );
}

/// Controla a listagem paginada de livros (scroll infinito).
/// Observa auth e filtros: recarrega a primeira pagina quando qualquer um muda
/// (evita reaproveitar erro 401 gerado antes do login).
class BooksListController extends AutoDisposeAsyncNotifier<BooksListState> {
  @override
  Future<BooksListState> build() async {
    ref.watch(authControllerProvider);
    final filters = ref.watch(bookFiltersProvider);
    final page = await _fetch(filters, 1);
    return BooksListState(
      items: page.items,
      currentPage: page.currentPage,
      lastPage: page.lastPage,
    );
  }

  Future<void> loadMore() async {
    final current = state.valueOrNull;
    if (current == null || !current.hasMore || current.loadingMore) return;

    state = AsyncData(current.copyWith(loadingMore: true));
    final filters = ref.read(bookFiltersProvider);
    try {
      final next = await _fetch(filters, current.currentPage + 1);
      state = AsyncData(BooksListState(
        items: [...current.items, ...next.items],
        currentPage: next.currentPage,
        lastPage: next.lastPage,
      ));
    } catch (_) {
      state = AsyncData(current.copyWith(loadingMore: false));
    }
  }

  Future<BooksPage> _fetch(BookFilters filters, int page) {
    return ref.read(bookRepositoryProvider).list(
          titulo: filters.titulo,
          tituloDoIndice: filters.tituloDoIndice,
          page: page,
        );
  }
}

final booksListProvider =
    AutoDisposeAsyncNotifierProvider<BooksListController, BooksListState>(
  BooksListController.new,
);

/// Detalhe de um livro por id.
final bookDetailProvider = FutureProvider.family<Book, int>((ref, id) {
  return ref.watch(bookRepositoryProvider).show(id);
});

/// Livros similares a um livro existente.
final similarBooksProvider = FutureProvider.family<List<Book>, int>((ref, id) {
  return ref.watch(bookRepositoryProvider).similarToBook(id);
});
