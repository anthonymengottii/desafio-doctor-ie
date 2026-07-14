/// Indice de um livro. Estrutura recursiva: cada indice pode ter subindices.
class BookIndex {
  BookIndex({
    this.id,
    required this.titulo,
    required this.pagina,
    this.subindices = const [],
  });

  final int? id;
  final String titulo;
  final int pagina;
  final List<BookIndex> subindices;

  factory BookIndex.fromJson(Map<String, dynamic> json) => BookIndex(
        id: json['id'] as int?,
        titulo: json['titulo'] as String? ?? '',
        pagina: json['pagina'] as int? ?? 0,
        subindices: (json['subindices'] as List<dynamic>? ?? [])
            .map((e) => BookIndex.fromJson(e as Map<String, dynamic>))
            .toList(),
      );

  /// Formato aceito pela API ao criar/editar (sem id).
  Map<String, dynamic> toPayload() => {
        'titulo': titulo,
        'pagina': pagina,
        'subindices': subindices.map((e) => e.toPayload()).toList(),
      };

  BookIndex copyWith({String? titulo, int? pagina, List<BookIndex>? subindices}) =>
      BookIndex(
        id: id,
        titulo: titulo ?? this.titulo,
        pagina: pagina ?? this.pagina,
        subindices: subindices ?? this.subindices,
      );
}
