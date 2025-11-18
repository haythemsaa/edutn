import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiClient {
  static const String baseUrl = 'https://api.edutnpro.tn/api';
  String? _token;

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

  // Dashboard API
  Future<Map<String, dynamic>> getDashboard() async {
    final response = await http.get(
      Uri.parse('$baseUrl/admin/dashboard'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Analytics API
  Future<Map<String, dynamic>> getAnalytics() async {
    final response = await http.get(
      Uri.parse('$baseUrl/admin/analytics'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Schools API
  Future<List<dynamic>> getSchools() async {
    final response = await http.get(
      Uri.parse('$baseUrl/admin/schools'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> getSchool(int schoolId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/admin/schools/$schoolId'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Students API
  Future<List<dynamic>> getStudents({int? schoolId}) async {
    var url = '$baseUrl/admin/students';
    if (schoolId != null) url += '?school_id=$schoolId';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Teachers API
  Future<List<dynamic>> getTeachers({int? schoolId}) async {
    var url = '$baseUrl/admin/teachers';
    if (schoolId != null) url += '?school_id=$schoolId';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Statistics API
  Future<Map<String, dynamic>> getStatistics({int? schoolId}) async {
    var url = '$baseUrl/admin/statistics';
    if (schoolId != null) url += '?school_id=$schoolId';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Reports API
  Future<List<dynamic>> getReports() async {
    final response = await http.get(
      Uri.parse('$baseUrl/admin/reports'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }
}
