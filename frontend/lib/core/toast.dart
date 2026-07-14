import 'package:flutter/material.dart';

/// Chave global do ScaffoldMessenger: permite exibir toasts (SnackBars) mesmo
/// apos fechar um modal ou navegar de tela.
final scaffoldMessengerKey = GlobalKey<ScaffoldMessengerState>();

void showToast(String mensagem) {
  final messenger = scaffoldMessengerKey.currentState;
  if (messenger == null) return;
  messenger
    ..clearSnackBars()
    ..showSnackBar(
      SnackBar(content: Text(mensagem), behavior: SnackBarBehavior.floating),
    );
}
