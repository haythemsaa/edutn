import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  static const String baseUrl = 'http://your-backend-url.com/api';
  final _storage = const FlutterSecureStorage();

  Future<Map<String, String>> _getHeaders() async {
    final token = await _storage.read(key: 'auth_token');
    final locale = await _storage.read(key: 'locale') ?? 'fr';

    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Accept-Language': locale,
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Authentication
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _getHeaders(),
      body: jsonEncode({'email': email, 'password': password}),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> logout() async {
    final response = await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: await _getHeaders(),
    );
    await _storage.delete(key: 'auth_token');
    return _handleResponse(response);
  }

  // Dashboard
  Future<Map<String, dynamic>> getDashboard() async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/dashboard'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Children (Students)
  Future<List<dynamic>> getMyChildren() async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/children'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Grades
  Future<List<dynamic>> getChildGrades(int studentId, {String? term}) async {
    var url = '$baseUrl/parent/students/$studentId/grades';
    if (term != null) url += '?term=$term';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Report Cards
  Future<Map<String, dynamic>> getReportCard(int studentId, String term) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/students/$studentId/report-cards/$term'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Attendance
  Future<Map<String, dynamic>> getAttendance({String? month}) async {
    var url = '$baseUrl/parent/attendance';
    if (month != null) url += '?month=$month';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<List<dynamic>> getChildAttendance(int studentId, {String? month}) async {
    var url = '$baseUrl/parent/students/$studentId/attendance';
    if (month != null) url += '?month=$month';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Messaging
  Future<List<dynamic>> getConversations() async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/conversations'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<List<dynamic>> getMessages(int conversationId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/conversations/$conversationId/messages'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> sendMessage(int conversationId, String content, {List<String>? attachments}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/parent/conversations/$conversationId/messages'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'content': content,
        'attachments': attachments ?? [],
      }),
    );
    return _handleResponse(response)['data'];
  }

  // Events
  Future<List<dynamic>> getEvents({String? type}) async {
    var url = '$baseUrl/parent/events';
    if (type != null) url += '?type=$type';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Assignments
  Future<List<dynamic>> getChildAssignments(int studentId, {String? status}) async {
    var url = '$baseUrl/parent/students/$studentId/assignments';
    if (status != null) url += '?status=$status';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Invoices & Payments
  Future<List<dynamic>> getInvoices({String? status}) async {
    var url = '$baseUrl/parent/invoices';
    if (status != null) url += '?status=$status';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> makePayment(int invoiceId, Map<String, dynamic> paymentData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/parent/invoices/$invoiceId/pay'),
      headers: await _getHeaders(),
      body: jsonEncode(paymentData),
    );
    return _handleResponse(response)['data'];
  }

  // Appointments
  Future<List<dynamic>> getAppointments() async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/appointments'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> requestAppointment(Map<String, dynamic> appointmentData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/parent/appointments'),
      headers: await _getHeaders(),
      body: jsonEncode(appointmentData),
    );
    return _handleResponse(response)['data'];
  }

  // Timetable
  Future<Map<String, dynamic>> getChildTimetable(int studentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/students/$studentId/timetable'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Certificates & Documents
  Future<List<dynamic>> getDocuments(int studentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/students/$studentId/documents'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> requestCertificate(int studentId, String certificateType) async {
    final response = await http.post(
      Uri.parse('$baseUrl/parent/students/$studentId/certificates/request'),
      headers: await _getHeaders(),
      body: jsonEncode({'certificate_type': certificateType}),
    );
    return _handleResponse(response)['data'];
  }

  // Transport
  Future<Map<String, dynamic>> getTransportInfo(int studentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/students/$studentId/transport'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> trackBus(int busId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/transport/buses/$busId/track'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Canteen
  Future<List<dynamic>> getCanteenMenus({String? date}) async {
    var url = '$baseUrl/parent/canteen/menus';
    if (date != null) url += '?date=$date';

    final response = await http.get(
      Uri.parse(url),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Discipline
  Future<List<dynamic>> getDisciplineIncidents(int studentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/students/$studentId/discipline'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  // Profile
  Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('$baseUrl/parent/profile'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response)['data'];
  }

  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/parent/profile'),
      headers: await _getHeaders(),
      body: jsonEncode(data),
    );
    return _handleResponse(response)['data'];
  }

  // Helper method to handle responses
  Map<String, dynamic> _handleResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    } else {
      throw Exception('API Error: ${response.statusCode} - ${response.body}');
    }
  }
}
