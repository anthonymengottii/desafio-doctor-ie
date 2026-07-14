/// Usuario publicador, espelhando o UserResource da API.
class User {
  User({required this.id, required this.nome, required this.email});

  final int id;
  final String nome;
  final String email;

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'] as int,
        nome: json['nome'] as String? ?? '',
        email: json['email'] as String? ?? '',
      );
}
