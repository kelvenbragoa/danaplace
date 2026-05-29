# Teste Prático da API de Autenticação

## Como Testar

### 1. Teste com cURL

```bash
# 1. Fazer login
curl -X POST "http://localhost:8001/api/auth/login" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "email": "admin@example.com",
       "password": "password",
       "device_name": "Teste Mobile"
     }'

# Salve o token da resposta para usar nos próximos comandos

# 2. Obter dados do usuário (substitua SEU_TOKEN pelo token recebido)
curl -X GET "http://localhost:8001/api/auth/me" \
     -H "Authorization: Bearer SEU_TOKEN" \
     -H "Accept: application/json"

# 3. Atualizar perfil
curl -X PUT "http://localhost:8001/api/auth/profile" \
     -H "Authorization: Bearer SEU_TOKEN" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "name": "Nome Atualizado"
     }'

# 4. Verificar token
curl -X GET "http://localhost:8001/api/auth/verify-token" \
     -H "Authorization: Bearer SEU_TOKEN" \
     -H "Accept: application/json"

# 5. Logout
curl -X POST "http://localhost:8001/api/auth/logout" \
     -H "Authorization: Bearer SEU_TOKEN" \
     -H "Accept: application/json"
```

### 2. Teste com JavaScript (Browser/Node.js)

```javascript
const API_BASE = 'http://localhost:8001/api';

// Função de teste completa
async function testarAPI() {
    try {
        // 1. Login
        console.log('1. Testando login...');
        const loginResponse = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: 'admin@example.com',
                password: 'password',
                device_name: 'Teste JavaScript'
            })
        });

        const loginData = await loginResponse.json();
        console.log('Login response:', loginData);

        if (!loginData.success) {
            throw new Error(loginData.message);
        }

        const token = loginData.data.token;
        console.log('Token obtido:', token.substring(0, 20) + '...');

        // 2. Obter dados do usuário
        console.log('\\n2. Testando obtenção de dados do usuário...');
        const userResponse = await fetch(`${API_BASE}/auth/me`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const userData = await userResponse.json();
        console.log('User data:', userData);

        // 3. Verificar token
        console.log('\\n3. Testando verificação de token...');
        const verifyResponse = await fetch(`${API_BASE}/auth/verify-token`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const verifyData = await verifyResponse.json();
        console.log('Token verification:', verifyData);

        // 4. Logout
        console.log('\\n4. Testando logout...');
        const logoutResponse = await fetch(`${API_BASE}/auth/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const logoutData = await logoutResponse.json();
        console.log('Logout response:', logoutData);

        console.log('\\n✅ Todos os testes passaram!');

    } catch (error) {
        console.error('❌ Erro no teste:', error.message);
    }
}

// Executar teste
testarAPI();
```

### 3. Exemplo de Integração Flutter Completa

```dart
// api_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String _baseUrl = 'https://seu-dominio.com/api';
  static const String _tokenKey = 'auth_token';
  
  String? _token;

  // Inicializar serviço (carregar token salvo)
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
  }

  // Salvar token
  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    _token = token;
  }

  // Remover token
  Future<void> _removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    _token = null;
  }

  // Headers padrão
  Map<String, String> _getHeaders({bool includeAuth = false}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (includeAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  // Tratamento de resposta
  Map<String, dynamic> _handleResponse(http.Response response) {
    final data = json.decode(response.body);
    
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return data;
    } else if (response.statusCode == 401) {
      _removeToken(); // Token inválido, remover
      throw UnauthorizedException(data['message'] ?? 'Não autorizado');
    } else if (response.statusCode == 422) {
      throw ValidationException(data['message'] ?? 'Dados inválidos', data['errors']);
    } else {
      throw ApiException(data['message'] ?? 'Erro no servidor');
    }
  }

  // Login
  Future<LoginResponse> login(String email, String password, {String? deviceName}) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/login'),
        headers: _getHeaders(),
        body: json.encode({
          'email': email,
          'password': password,
          'device_name': deviceName ?? 'Flutter App',
        }),
      ).timeout(const Duration(seconds: 30));

      final data = _handleResponse(response);
      
      if (data['success']) {
        await _saveToken(data['data']['token']);
        return LoginResponse.fromJson(data['data']);
      } else {
        throw ApiException(data['message']);
      }
    } on SocketException {
      throw NetworkException('Sem conexão com a internet');
    } catch (e) {
      rethrow;
    }
  }

  // Obter dados do usuário
  Future<User> getUserData() async {
    final response = await http.get(
      Uri.parse('$_baseUrl/auth/me'),
      headers: _getHeaders(includeAuth: true),
    ).timeout(const Duration(seconds: 30));

    final data = _handleResponse(response);
    return User.fromJson(data['data']['user']);
  }

  // Logout
  Future<void> logout() async {
    try {
      await http.post(
        Uri.parse('$_baseUrl/auth/logout'),
        headers: _getHeaders(includeAuth: true),
      ).timeout(const Duration(seconds: 30));
    } finally {
      await _removeToken();
    }
  }

  // Verificar se está logado
  bool get isLoggedIn => _token != null;

  // Atualizar perfil
  Future<User> updateProfile({String? name, String? email}) async {
    final body = <String, dynamic>{};
    if (name != null) body['name'] = name;
    if (email != null) body['email'] = email;

    final response = await http.put(
      Uri.parse('$_baseUrl/auth/profile'),
      headers: _getHeaders(includeAuth: true),
      body: json.encode(body),
    ).timeout(const Duration(seconds: 30));

    final data = _handleResponse(response);
    return User.fromJson(data['data']['user']);
  }
}

// Modelos
class LoginResponse {
  final User user;
  final String token;
  final String tokenType;

  LoginResponse({
    required this.user,
    required this.token,
    required this.tokenType,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    return LoginResponse(
      user: User.fromJson(json['user']),
      token: json['token'],
      tokenType: json['token_type'],
    );
  }
}

class User {
  final int id;
  final String name;
  final String email;
  final String? role;
  final String? avatar;
  final DateTime createdAt;
  final DateTime? lastLoginAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.role,
    this.avatar,
    required this.createdAt,
    this.lastLoginAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'],
      avatar: json['avatar'],
      createdAt: DateTime.parse(json['created_at']),
      lastLoginAt: json['last_login_at'] != null 
          ? DateTime.parse(json['last_login_at']) 
          : null,
    );
  }
}

// Exceções personalizadas
class ApiException implements Exception {
  final String message;
  ApiException(this.message);
}

class UnauthorizedException extends ApiException {
  UnauthorizedException(String message) : super(message);
}

class ValidationException extends ApiException {
  final Map<String, dynamic>? errors;
  ValidationException(String message, this.errors) : super(message);
}

class NetworkException extends ApiException {
  NetworkException(String message) : super(message);
}

// Exemplo de uso em um widget
class LoginPage extends StatefulWidget {
  @override
  _LoginPageState createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _apiService = ApiService();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _apiService.init();
  }

  Future<void> _login() async {
    setState(() => _isLoading = true);

    try {
      final response = await _apiService.login(
        _emailController.text,
        _passwordController.text,
      );

      // Login bem-sucedido
      Navigator.pushReplacementNamed(context, '/home');
      
    } on ValidationException catch (e) {
      _showError('Dados inválidos: ${e.message}');
    } on UnauthorizedException catch (e) {
      _showError('Email ou senha incorretos');
    } on NetworkException catch (e) {
      _showError('Problema de conexão. Tente novamente.');
    } on ApiException catch (e) {
      _showError(e.message);
    } catch (e) {
      _showError('Erro inesperado: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Login')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          children: [
            TextField(
              controller: _emailController,
              decoration: InputDecoration(labelText: 'Email'),
              keyboardType: TextInputType.emailAddress,
            ),
            TextField(
              controller: _passwordController,
              decoration: InputDecoration(labelText: 'Senha'),
              obscureText: true,
            ),
            SizedBox(height: 20),
            ElevatedButton(
              onPressed: _isLoading ? null : _login,
              child: _isLoading 
                ? CircularProgressIndicator() 
                : Text('Entrar'),
            ),
          ],
        ),
      ),
    );
  }
}
```

## Próximos Passos para Produção

1. **Configurar CORS** para permitir requisições do mobile
2. **Configurar Rate Limiting** para prevenir ataques
3. **Implementar Logs de Segurança**
4. **Configurar SSL/TLS** (HTTPS obrigatório)
5. **Implementar Refresh Tokens** se necessário
6. **Configurar Monitoramento** de API