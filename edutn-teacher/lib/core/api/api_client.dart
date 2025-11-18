import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiClient {
  static const String baseUrl = 'https://api.edutnpro.tn/api';
  String? _token;

  // Authentication methods would be here...

  Future<Map<String, String>> _getHeaders() async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  Map<String, dynamic> _handleResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Request failed: ${response.statusCode}');
    }
  }

  // Documents API
  Future<List<dynamic>> getTeacherDocuments() async {
    final response = await http.get(
      Uri.parse('$baseUrl/teacher/documents'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> getDocument(int documentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/teacher/documents/$documentId'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<void> downloadDocument(int documentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/teacher/documents/$documentId/download'),
      headers: await _getHeaders(),
    );
    _handleResponse(response);
  }

  // Add more API methods as needed...
}
