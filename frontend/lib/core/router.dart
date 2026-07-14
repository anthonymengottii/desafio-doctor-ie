import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/auth_providers.dart';
import '../features/books/screens/book_detail_screen.dart';
import '../features/books/screens/book_form_screen.dart';
import '../features/books/screens/books_list_screen.dart';
import '../features/books/screens/similar_screen.dart';
import '../features/auth/screens/login_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final auth = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/books',
    redirect: (context, state) {
      // Sem usuario resolvido (carregando no boot ou deslogado) => login.
      // Evita montar a lista antes de ter token e disparar um 401 na tela.
      final logged = auth.valueOrNull != null;
      final onLogin = state.matchedLocation == '/login';

      if (!logged) return onLogin ? null : '/login';
      if (onLogin) return '/books';
      return null;
    },
    routes: [
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/books', builder: (_, __) => const BooksListScreen()),
      GoRoute(path: '/books/new', builder: (_, __) => const BookFormScreen()),
      GoRoute(
        path: '/books/:id',
        builder: (_, s) => BookDetailScreen(id: int.parse(s.pathParameters['id']!)),
      ),
      GoRoute(
        path: '/books/:id/edit',
        builder: (_, s) => BookFormScreen(bookId: int.parse(s.pathParameters['id']!)),
      ),
      GoRoute(
        path: '/books/:id/similar',
        builder: (_, s) => SimilarScreen(bookId: int.parse(s.pathParameters['id']!)),
      ),
    ],
  );
});
