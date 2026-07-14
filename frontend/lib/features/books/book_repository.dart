import '../../core/api_client.dart';
import 'models/book.dart';
import 'models/book_index.dart';
import 'models/books_page.dart';

/// Acesso aos endpoints de livros, indices e similaridade.
class BookRepository {
  BookRepository(this._api);

  final ApiClient _api;

  Future<BooksPage> list({
    String? titulo,
    String? tituloDoIndice,
    int page = 1,
    int perPage = 20,
  }) async {
    final query = <String, dynamic>{'page': page, 'per_page': perPage};
    if (titulo != null && titulo.isNotEmpty) query['titulo'] = titulo;
    if (tituloDoIndice != null && tituloDoIndice.isNotEmpty) {
      query['titulo_do_indice'] = tituloDoIndice;
    }
    final res = await _api.get('/books', query: query);
    return BooksPage.fromResponse(res.data);
  }

  Future<Book> show(int id) async {
    final res = await _api.get('/books/$id');
    return Book.fromJson(_unwrap(res.data));
  }

  Future<Book> create({
    required String titulo,
    required int numeroPaginas,
    required List<BookIndex> indices,
  }) async {
    final res = await _api.post('/books', data: {
      'titulo': titulo,
      'numero_paginas': numeroPaginas,
      'indices': indices.map((e) => e.toPayload()).toList(),
    });
    return Book.fromJson(_unwrap(res.data));
  }

  Future<Book> update(
    int id, {
    required String titulo,
    required int numeroPaginas,
    required List<BookIndex> indices,
  }) async {
    final res = await _api.put('/books/$id', data: {
      'titulo': titulo,
      'numero_paginas': numeroPaginas,
      'indices': indices.map((e) => e.toPayload()).toList(),
    });
    return Book.fromJson(_unwrap(res.data));
  }

  Future<void> delete(int id) => _api.delete('/books/$id');

  Future<List<Book>> similarByText(String titulo) async {
    final res = await _api.get('/books/similares', query: {'titulo': titulo});
    return _parseList(res.data);
  }

  Future<List<Book>> similarToBook(int id) async {
    final res = await _api.get('/books/$id/similares');
    return _parseList(res.data);
  }

  List<Book> _parseList(dynamic data) {
    final list = data is Map<String, dynamic> ? data['data'] as List<dynamic> : data as List<dynamic>;
    return list.map((e) => Book.fromJson(e as Map<String, dynamic>)).toList();
  }

  Map<String, dynamic> _unwrap(dynamic data) {
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return data['data'] as Map<String, dynamic>;
    }
    return data as Map<String, dynamic>;
  }
}
