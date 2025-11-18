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

  // Assignments API
  Future<List<dynamic>> getAssignments() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/assignments'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> getAssignment(int assignmentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/assignments/$assignmentId'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> submitAssignment(
    int assignmentId, {
    String? content,
    String? attachmentPath,
  }) async {
    final body = <String, dynamic>{};
    if (content != null) body['content'] = content;
    if (attachmentPath != null) body['attachment_path'] = attachmentPath;

    final response = await http.post(
      Uri.parse('$baseUrl/student/assignments/$assignmentId/submit'),
      headers: await _getHeaders(),
      body: jsonEncode(body),
    );
    return _handleResponse(response)['data'];
  }

  // Grades API
  Future<List<dynamic>> getGrades() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/grades'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Timetable API
  Future<List<dynamic>> getTimetable() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/timetable'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Profile API
  Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/profile'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Attendance API
  Future<List<dynamic>> getAttendance() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/attendance'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Events API
  Future<List<dynamic>> getEvents() async {
    final response = await http.get(
      Uri.parse('$baseUrl/student/events'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }
}
