import 'package:flutter_test/flutter_test.dart';
import 'package:frontend/features/books/models/book.dart';
import 'package:frontend/features/books/models/book_index.dart';

void main() {
  group('Parsing dos models (contrato da API)', () {
    test('BookIndex parseia subindices recursivamente', () {
      final json = {
        'id': 1,
        'titulo': 'Capitulo 1',
        'pagina': 1,
        'subindices': [
          {'id': 2, 'titulo': 'Introducao', 'pagina': 2, 'subindices': []},
        ],
      };

      final index = BookIndex.fromJson(json);

      expect(index.titulo, 'Capitulo 1');
      expect(index.subindices, hasLength(1));
      expect(index.subindices.first.titulo, 'Introducao');
    });

    test('Book parseia usuario_publicador e arvore de indices', () {
      final json = {
        'id': 10,
        'titulo': 'Clean Code',
        'numero_paginas': 450,
        'usuario_publicador': {'id': 1, 'nome': 'Bill', 'email': 'bill@example.com'},
        'indices': [
          {
            'titulo': 'Capitulo 1',
            'pagina': 1,
            'subindices': [
              {'titulo': 'Introducao', 'pagina': 2, 'subindices': []},
            ],
          },
        ],
      };

      final book = Book.fromJson(json);

      expect(book.titulo, 'Clean Code');
      expect(book.numeroPaginas, 450);
      expect(book.usuarioPublicador?.nome, 'Bill');
      expect(book.indices.first.subindices.first.titulo, 'Introducao');
    });

    test('toPayload gera formato aceito pela API (sem id)', () {
      final index = BookIndex(
        id: 5,
        titulo: 'Parte I',
        pagina: 10,
        subindices: [BookIndex(titulo: 'Cap 1', pagina: 11)],
      );

      final payload = index.toPayload();

      expect(payload.containsKey('id'), isFalse);
      expect(payload['titulo'], 'Parte I');
      expect((payload['subindices'] as List).first['titulo'], 'Cap 1');
    });
  });
}
