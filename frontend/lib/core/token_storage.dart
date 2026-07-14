import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Persiste o token de autenticacao.
///
/// Mantem tambem um cache em memoria: dentro da sessao atual o token sempre
/// vem da memoria, evitando falhas de leitura do armazenamento seguro no web
/// (que poderiam deslogar o usuario logo apos o login). O armazenamento seguro
/// serve para restaurar a sessao ao reabrir o app.
class TokenStorage {
  TokenStorage(this._storage);

  final FlutterSecureStorage _storage;
  static const _key = 'auth_token';

  String? _cache;
  bool _loaded = false;

  Future<String?> read() async {
    if (_cache != null) return _cache;
    if (_loaded) return _cache;
    try {
      _cache = await _storage.read(key: _key);
    } catch (_) {
      _cache = null;
    }
    _loaded = true;
    return _cache;
  }

  Future<void> write(String token) async {
    _cache = token;
    _loaded = true;
    try {
      await _storage.write(key: _key, value: token);
    } catch (_) {
      // Persistencia e best-effort; a sessao atual continua via cache.
    }
  }

  Future<void> clear() async {
    _cache = null;
    _loaded = true;
    try {
      await _storage.delete(key: _key);
    } catch (_) {}
  }
}
