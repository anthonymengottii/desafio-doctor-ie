import 'package:flutter/material.dart';

import '../models/book_index.dart';

/// Exibe a arvore de indices de forma recursiva, recuando cada nivel.
class IndexTreeView extends StatelessWidget {
  const IndexTreeView({super.key, required this.indices});

  final List<BookIndex> indices;

  @override
  Widget build(BuildContext context) {
    if (indices.isEmpty) {
      return const Padding(
        padding: EdgeInsets.all(8),
        child: Text('Sem indices cadastrados.'),
      );
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [for (final node in indices) _IndexNode(node: node, depth: 0)],
    );
  }
}

class _IndexNode extends StatelessWidget {
  const _IndexNode({required this.node, required this.depth});

  final BookIndex node;
  final int depth;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: EdgeInsets.only(left: 16.0 * depth, top: 4, bottom: 4),
          child: Row(
            children: [
              const Icon(Icons.chevron_right, size: 18),
              Expanded(child: Text(node.titulo)),
              Text('p. ${node.pagina}',
                  style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
        ),
        for (final child in node.subindices)
          _IndexNode(node: child, depth: depth + 1),
      ],
    );
  }
}
