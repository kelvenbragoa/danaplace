# API de Autenticação - MDO Areiabranca

Esta documentação descreve como integrar a API de autenticação do sistema MDO Areiabranca com aplicações mobile.

## Base URL

```
https://seu-dominio.com/api
```

## Autenticação

A API utiliza Laravel Sanctum para autenticação baseada em tokens. Após o login, você receberá um token que deve ser incluído no header `Authorization` de todas as requisições protegidas.

### Header de Autorização
```
Authorization: Bearer {seu-token-aqui}
```

### Headers Obrigatórios
```
Content-Type: application/json
Accept: application/json
```

---

## Endpoints

### 1. Login

Autentica um usuário e retorna um token de acesso.

**Endpoint:** `POST /auth/login`

**Parâmetros:**
```json
{
    "email": "string (required)",
    "password": "string (required, min:6)",
    "device_name": "string (optional, max:255)"
}
```

**Exemplo de Requisição:**
```bash
curl -X POST "https://seu-dominio.com/api/auth/login" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "email": "user@example.com",
       "password": "password123",
       "device_name": "iPhone 14 Pro"
     }'
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Login realizado com sucesso",
    "data": {
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "user@example.com",
            "role": "technician",
            "avatar": null,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "last_login_at": "2024-01-15T10:30:00.000000Z"
        },
        "token": "1|ABC123...XYZ789",
        "token_type": "Bearer",
        "expires_at": null
    }
}
```

**Resposta de Erro (401):**
```json
{
    "success": false,
    "message": "Email ou senha incorretos"
}
```

**Resposta de Erro (422):**
```json
{
    "success": false,
    "message": "Dados de entrada inválidos",
    "errors": {
        "email": ["O campo email é obrigatório."],
        "password": ["O campo password deve ter pelo menos 6 caracteres."]
    }
}
```

### 2. Logout

Invalida o token atual do usuário.

**Endpoint:** `POST /auth/logout`

**Headers:** Requer autenticação (`Authorization: Bearer {token}`)

**Exemplo de Requisição:**
```bash
curl -X POST "https://seu-dominio.com/api/auth/logout" \
     -H "Authorization: Bearer 1|ABC123...XYZ789" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Logout realizado com sucesso"
}
```

### 3. Logout de Todos os Dispositivos

Invalida todos os tokens do usuário.

**Endpoint:** `POST /auth/logout-all`

**Headers:** Requer autenticação (`Authorization: Bearer {token}`)

**Exemplo de Requisição:**
```bash
curl -X POST "https://seu-dominio.com/api/auth/logout-all" \
     -H "Authorization: Bearer 1|ABC123...XYZ789" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Logout realizado em todos os dispositivos com sucesso"
}
```

### 4. Obter Dados do Usuário Autenticado

Retorna os dados do usuário atualmente logado.

**Endpoint:** `GET /auth/me`

**Headers:** Requer autenticação (`Authorization: Bearer {token}`)

**Exemplo de Requisição:**
```bash
curl -X GET "https://seu-dominio.com/api/auth/me" \
     -H "Authorization: Bearer 1|ABC123...XYZ789" \
     -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "user@example.com",
            "role": "technician",
            "avatar": null,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z",
            "last_login_at": "2024-01-15T10:30:00.000000Z",
            "email_verified_at": "2024-01-01T00:00:00.000000Z"
        }
    }
}
```

### 5. Atualizar Perfil

Atualiza os dados do perfil do usuário autenticado.

**Endpoint:** `PUT /auth/profile`

**Headers:** Requer autenticação (`Authorization: Bearer {token}`)

**Parâmetros:**
```json
{
    "name": "string (optional, max:255)",
    "email": "string (optional, email, unique)",
    "current_password": "string (required_with:password)",
    "password": "string (optional, min:6)",
    "password_confirmation": "string (required_with:password)"
}
```

**Exemplo de Requisição:**
```bash
curl -X PUT "https://seu-dominio.com/api/auth/profile" \
     -H "Authorization: Bearer 1|ABC123...XYZ789" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "name": "João Silva Santos",
       "email": "joao.santos@example.com"
     }'
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Perfil atualizado com sucesso",
    "data": {
        "user": {
            "id": 1,
            "name": "João Silva Santos",
            "email": "joao.santos@example.com",
            "role": "technician",
            "updated_at": "2024-01-15T10:35:00.000000Z"
        }
    }
}
```

### 6. Verificar Token

Verifica se o token atual é válido.

**Endpoint:** `GET /auth/verify-token`

**Headers:** Requer autenticação (`Authorization: Bearer {token}`)

**Exemplo de Requisição:**
```bash
curl -X GET "https://seu-dominio.com/api/auth/verify-token" \
     -H "Authorization: Bearer 1|ABC123...XYZ789" \
     -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Token válido",
    "data": {
        "user_id": 1,
        "token_name": "iPhone 14 Pro",
        "expires_at": null
    }
}
```

---

## Códigos de Status HTTP

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 401 | Não autorizado (credenciais inválidas ou token expirado) |
| 403 | Proibido (conta desativada) |
| 422 | Dados de entrada inválidos |
| 500 | Erro interno do servidor |

---

## Integração com Aplicação Mobile

### 1. Implementação no Flutter

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  final String baseUrl = 'https://seu-dominio.com/api';
  String? _token;

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'email': email,
        'password': password,
        'device_name': 'Flutter App',
      }),
    );

    final data = json.decode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      _token = data['data']['token'];
      // Salvar token localmente (SharedPreferences, etc.)
      return data;
    } else {
      throw Exception(data['message'] ?? 'Erro no login');
    }
  }

  // Fazer requisição autenticada
  Future<http.Response> authenticatedRequest(
    String method, 
    String endpoint, 
    {Map<String, dynamic>? body}
  ) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer $_token',
    };

    switch (method.toUpperCase()) {
      case 'GET':
        return await http.get(uri, headers: headers);
      case 'POST':
        return await http.post(uri, headers: headers, body: json.encode(body));
      case 'PUT':
        return await http.put(uri, headers: headers, body: json.encode(body));
      case 'DELETE':
        return await http.delete(uri, headers: headers);
      default:
        throw Exception('Método HTTP não suportado');
    }
  }

  // Obter dados do usuário
  Future<Map<String, dynamic>> getUserData() async {
    final response = await authenticatedRequest('GET', '/auth/me');
    return json.decode(response.body);
  }

  // Logout
  Future<void> logout() async {
    await authenticatedRequest('POST', '/auth/logout');
    _token = null;
    // Remover token do armazenamento local
  }
}
```

### 2. Implementação no React Native

```javascript
class ApiService {
  constructor() {
    this.baseUrl = 'https://seu-dominio.com/api';
    this.token = null;
  }

  async login(email, password) {
    try {
      const response = await fetch(`${this.baseUrl}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          email,
          password,
          device_name: 'React Native App',
        }),
      });

      const data = await response.json();

      if (response.ok && data.success) {
        this.token = data.data.token;
        // Salvar token no AsyncStorage
        await AsyncStorage.setItem('auth_token', this.token);
        return data;
      } else {
        throw new Error(data.message || 'Erro no login');
      }
    } catch (error) {
      throw error;
    }
  }

  async authenticatedRequest(method, endpoint, body = null) {
    const config = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${this.token}`,
      },
    };

    if (body) {
      config.body = JSON.stringify(body);
    }

    return await fetch(`${this.baseUrl}${endpoint}`, config);
  }

  async getUserData() {
    const response = await this.authenticatedRequest('GET', '/auth/me');
    return await response.json();
  }

  async logout() {
    await this.authenticatedRequest('POST', '/auth/logout');
    this.token = null;
    await AsyncStorage.removeItem('auth_token');
  }
}
```

### 3. Implementação no Swift (iOS)

```swift
import Foundation

class ApiService {
    private let baseUrl = "https://seu-dominio.com/api"
    private var token: String?
    
    func login(email: String, password: String, completion: @escaping (Result<[String: Any], Error>) -> Void) {
        guard let url = URL(string: "\(baseUrl)/auth/login") else { return }
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        
        let body = [
            "email": email,
            "password": password,
            "device_name": "iOS App"
        ]
        
        do {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        } catch {
            completion(.failure(error))
            return
        }
        
        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(error))
                return
            }
            
            guard let data = data else { return }
            
            do {
                let json = try JSONSerialization.jsonObject(with: data) as? [String: Any]
                if let success = json?["success"] as? Bool, success,
                   let dataDict = json?["data"] as? [String: Any],
                   let token = dataDict["token"] as? String {
                    self.token = token
                    // Salvar token no Keychain
                    completion(.success(json!))
                } else {
                    let message = json?["message"] as? String ?? "Erro no login"
                    completion(.failure(NSError(domain: "", code: 0, userInfo: [NSLocalizedDescriptionKey: message])))
                }
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }
    
    func authenticatedRequest(method: String, endpoint: String, body: [String: Any]? = nil, completion: @escaping (Result<Data, Error>) -> Void) {
        guard let url = URL(string: "\(baseUrl)\(endpoint)"),
              let token = self.token else { return }
        
        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        
        if let body = body {
            do {
                request.httpBody = try JSONSerialization.data(withJSONObject: body)
            } catch {
                completion(.failure(error))
                return
            }
        }
        
        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(error))
                return
            }
            
            guard let data = data else { return }
            completion(.success(data))
        }.resume()
    }
}
```

---

## Tratamento de Erros

### Erros Comuns

1. **Token Expirado/Inválido (401)**
   - Redirecionar para tela de login
   - Limpar token armazenado localmente

2. **Conta Desativada (403)**
   - Exibir mensagem informativa
   - Impossibilitar acesso até reativação

3. **Dados Inválidos (422)**
   - Exibir erros de validação nos campos correspondentes
   - Permitir correção e nova tentativa

4. **Erro de Rede (500)**
   - Exibir mensagem de erro genérica
   - Implementar retry automático quando apropriado

### Exemplo de Tratamento no Flutter

```dart
try {
  final userData = await apiService.getUserData();
  // Usar dados do usuário
} catch (e) {
  if (e.toString().contains('401')) {
    // Token inválido - redirecionar para login
    Navigator.pushReplacementNamed(context, '/login');
  } else {
    // Outros erros
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Erro'),
        content: Text(e.toString()),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }
}
```

---

## Considerações de Segurança

1. **Armazenamento Seguro do Token**
   - Use Keychain (iOS) ou Keystore (Android)
   - Nunca armazene tokens em texto plano

2. **HTTPS Obrigatório**
   - Sempre use HTTPS em produção
   - Implemente certificate pinning quando possível

3. **Timeout de Requests**
   - Configure timeouts apropriados (15-30 segundos)
   - Implemente retry com backoff exponencial

4. **Logs de Segurança**
   - Não registre tokens ou senhas em logs
   - Monitore tentativas de login suspeitas

---

## Testes da API

Você pode testar a API usando ferramentas como Postman, Insomnia ou curl. Aqui está uma coleção de exemplo para Postman:

```json
{
  "info": {
    "name": "MDO Areiabranca API",
    "description": "API de autenticação"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "https://seu-dominio.com/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/auth/login",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"user@example.com\",\n  \"password\": \"password123\",\n  \"device_name\": \"Postman\"\n}"
        }
      }
    },
    {
      "name": "Get User Data",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/auth/me",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    }
  ]
}
```

Esta documentação fornece todos os detalhes necessários para integrar a API de autenticação com qualquer aplicação mobile. Certifique-se de adaptar os exemplos de código às necessidades específicas da sua aplicação.