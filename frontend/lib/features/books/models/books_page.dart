import 'book.dart';

/// Uma pagina de resultados de livros (dados + metadados de paginacao).
class BooksPage {
  BooksPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
  });

  final List<Book> items;
  final int currentPage;
  final int lastPage;

  bool get hasMore => currentPage < lastPage;

  factory BooksPage.fromResponse(dynamic data) {
    final list = (data is Map<String, dynamic> ? data['data'] : data) as List<dynamic>;
    final items = list.map((e) => Book.fromJson(e as Map<String, dynamic>)).toList();

    final meta = data is Map<String, dynamic> ? data['meta'] as Map<String, dynamic>? : null;
    return BooksPage(
      items: items,
      currentPage: (meta?['current_page'] as int?) ?? 1,
      lastPage: (meta?['last_page'] as int?) ?? 1,
    );
  }
}
