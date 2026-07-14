import 'package:dio/dio.dart';

import 'config.dart';
import 'token_storage.dart';

/// Erro de API ja traduzido para exibicao ao usuario.
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

/// Cliente HTTP central. Injeta o Bearer token e normaliza os erros da API
/// no formato { "error": "..." } retornado pelo backend Laravel.
class ApiClient {
  ApiClient(this._tokens) {
    _dio = Dio(BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      headers: {'Accept': 'application/json'},
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _tokens.read();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) => handler.reject(_translate(error)),
    ));
  }

  late final Dio _dio;
  final TokenStorage _tokens;

  Future<Response<dynamic>> get(String path, {Map<String, dynamic>? query}) =>
      _dio.get(path, queryParameters: query);

  Future<Response<dynamic>> post(String path, {Object? data}) =>
      _dio.post(path, data: data);

  Future<Response<dynamic>> put(String path, {Object? data}) =>
      _dio.put(path, data: data);

  Future<Response<dynamic>> delete(String path) => _dio.delete(path);

  DioException _translate(DioException error) {
    final data = error.response?.data;
    var message = 'Falha na comunicacao com o servidor';

    if (data is Map && data['error'] is String) {
      message = data['error'] as String;
    }

    return error.copyWith(
      error: ApiException(message, statusCode: error.response?.statusCode),
    );
  }
}
