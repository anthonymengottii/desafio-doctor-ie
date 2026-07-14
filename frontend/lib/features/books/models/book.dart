import '../../auth/models/user.dart';
import 'book_index.dart';

/// Livro, espelhando o BookResource da API.
class Book {
  Book({
    required this.id,
    required this.titulo,
    required this.numeroPaginas,
    this.usuarioPublicador,
    this.indices = const [],
  });

  final int id;
  final String titulo;
  final int numeroPaginas;
  final User? usuarioPublicador;
  final List<BookIndex> indices;

  factory Book.fromJson(Map<String, dynamic> json) => Book(
        id: json['id'] as int,
        titulo: json['titulo'] as String? ?? '',
        numeroPaginas: json['numero_paginas'] as int? ?? 0,
        usuarioPublicador: json['usuario_publicador'] is Map<String, dynamic>
            ? User.fromJson(json['usuario_publicador'] as Map<String, dynamic>)
            : null,
        indices: (json['indices'] as List<dynamic>? ?? [])
            .map((e) => BookIndex.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}
