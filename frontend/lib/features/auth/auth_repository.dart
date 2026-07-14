import 'package:dio/dio.dart';

import '../../core/api_client.dart';
import '../../core/token_storage.dart';
import 'models/user.dart';

/// Acesso aos endpoints de autenticacao.
class AuthRepository {
  AuthRepository(this._api, this._tokens);

  final ApiClient _api;
  final TokenStorage _tokens;

  Future<User> register(String nome, String email, String senha) async {
    final res = await _api.post('/auth/register', data: {
      'name': nome,
      'email': email,
      'password': senha,
    });
    return _persist(res.data as Map<String, dynamic>);
  }

  Future<User> login(String email, String senha) async {
    final res = await _api.post('/auth/login', data: {
      'email': email,
      'password': senha,
    });
    return _persist(res.data as Map<String, dynamic>);
  }

  Future<User?> currentUser() async {
    if (await _tokens.read() == null) return null;
    try {
      final res = await _api.get('/auth/me');
      return User.fromJson((res.data as Map<String, dynamic>)['user'] as Map<String, dynamic>);
    } on DioException catch (e) {
      // Token invalido/expirado: limpa e trata como deslogado (sem erro).
      if (e.response?.statusCode == 401) {
        await _tokens.clear();
        return null;
      }
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } finally {
      await _tokens.clear();
    }
  }

  Future<User> _persist(Map<String, dynamic> data) async {
    await _tokens.write(data['token'] as String);
    return User.fromJson(data['user'] as Map<String, dynamic>);
  }
}
