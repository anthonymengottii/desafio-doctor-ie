/// Configuracao da aplicacao.
///
/// A URL base da API pode ser sobrescrita em tempo de build:
///   flutter run --dart-define=API_BASE_URL=http://192.168.0.10:8000/api
class AppConfig {
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api',
  );
}
