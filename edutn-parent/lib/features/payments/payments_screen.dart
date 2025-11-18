import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';
import 'payment_model.dart';

class PaymentsScreen extends StatefulWidget {
  const PaymentsScreen({Key? key}) : super(key: key);

  @override
  State<PaymentsScreen> createState() => _PaymentsScreenState();
}

class _PaymentsScreenState extends State<PaymentsScreen> with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  late TabController _tabController;
  List<Invoice> _allInvoices = [];
  PaymentStats? _stats;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadPayments();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadPayments() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getInvoices();
      setState(() {
        _allInvoices = data.map((json) => Invoice.fromJson(json)).toList();
        _stats = _calculateStats(_allInvoices);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  PaymentStats _calculateStats(List<Invoice> invoices) {
    final totalAmount = invoices.fold<double>(0.0, (sum, inv) => sum + inv.amount);
    final paidAmount = invoices.fold<double>(0.0, (sum, inv) => sum + inv.paidAmount);
    final pendingAmount = invoices.where((inv) => inv.isPending).fold<double>(0.0, (sum, inv) => sum + inv.remainingAmount);
    final overdueAmount = invoices.where((inv) => inv.isOverdue).fold<double>(0.0, (sum, inv) => sum + inv.remainingAmount);

    return PaymentStats(
      totalAmount: totalAmount,
      paidAmount: paidAmount,
      pendingAmount: pendingAmount,
      overdueAmount: overdueAmount,
      totalInvoices: invoices.length,
      paidInvoices: invoices.where((inv) => inv.isPaid).length,
      pendingInvoices: invoices.where((inv) => inv.isPending || inv.isPartiallyPaid).length,
      overdueInvoices: invoices.where((inv) => inv.isOverdue).length,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paiements'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'En attente'),
            Tab(text: 'Payées'),
            Tab(text: 'En retard'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : RefreshIndicator(
                  onRefresh: _loadPayments,
                  child: Column(
                    children: [
                      if (_stats != null) _buildStatsCard(),
                      Expanded(
                        child: TabBarView(
                          controller: _tabController,
                          children: [
                            _buildInvoicesList(_allInvoices.where((inv) => inv.isPending || inv.isPartiallyPaid).toList()),
                            _buildInvoicesList(_allInvoices.where((inv) => inv.isPaid).toList()),
                            _buildInvoicesList(_allInvoices.where((inv) => inv.isOverdue).toList()),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: Colors.red),
          const SizedBox(height: 16),
          Text('Erreur: $_error'),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadPayments,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsCard() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Résumé financier',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildStatItem(
                  'Total',
                  '${_stats!.totalAmount.toStringAsFixed(2)} TND',
                  Colors.blue,
                ),
                _buildStatItem(
                  'Payé',
                  '${_stats!.paidAmount.toStringAsFixed(2)} TND',
                  Colors.green,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildStatItem(
                  'En attente',
                  '${_stats!.pendingAmount.toStringAsFixed(2)} TND',
                  Colors.orange,
                ),
                _buildStatItem(
                  'En retard',
                  '${_stats!.overdueAmount.toStringAsFixed(2)} TND',
                  Colors.red,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildInvoicesList(List<Invoice> invoices) {
    if (invoices.isEmpty) {
      return const Center(
        child: Text('Aucune facture'),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: invoices.length,
      itemBuilder: (context, index) {
        return _buildInvoiceCard(invoices[index]);
      },
    );
  }

  Widget _buildInvoiceCard(Invoice invoice) {
    final statusColor = _getStatusColor(invoice.status);
    final statusLabel = _getStatusLabel(invoice.status);
    final typeIcon = _getTypeIcon(invoice.type);
    final typeLabel = _getTypeLabel(invoice.type);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () => _showInvoiceDetails(invoice),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    backgroundColor: statusColor.withOpacity(0.2),
                    child: Icon(typeIcon, color: statusColor),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          typeLabel,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          invoice.invoiceNumber,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      statusLabel,
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Montant',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      Text(
                        '${invoice.amount.toStringAsFixed(2)} TND',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  if (!invoice.isPaid) ...[
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'Restant',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                        Text(
                          '${invoice.remainingAmount.toStringAsFixed(2)} TND',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: invoice.isOverdue ? Colors.red : Colors.orange,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    'Échéance: ${DateFormat('dd/MM/yyyy').format(invoice.dueDate)}',
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                  if (invoice.isOverdue) ...[
                    const SizedBox(width: 8),
                    Text(
                      '(${invoice.daysOverdue} jours de retard)',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.red,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ] else if (!invoice.isPaid && invoice.daysUntilDue <= 7) ...[
                    const SizedBox(width: 8),
                    Text(
                      '(${invoice.daysUntilDue} jours restants)',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.orange,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ],
              ),
              if (!invoice.isPaid) ...[
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _initiatePayment(invoice),
                    icon: const Icon(Icons.payment),
                    label: const Text('Payer en ligne'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'paid':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'partial':
        return Colors.blue;
      case 'overdue':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'paid':
        return 'Payée';
      case 'pending':
        return 'En attente';
      case 'partial':
        return 'Partiel';
      case 'overdue':
        return 'En retard';
      default:
        return status;
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type) {
      case 'tuition':
        return Icons.school;
      case 'registration':
        return Icons.app_registration;
      case 'transport':
        return Icons.directions_bus;
      case 'canteen':
        return Icons.restaurant;
      default:
        return Icons.receipt;
    }
  }

  String _getTypeLabel(String type) {
    switch (type) {
      case 'tuition':
        return 'Frais de scolarité';
      case 'registration':
        return 'Frais d\'inscription';
      case 'transport':
        return 'Transport scolaire';
      case 'canteen':
        return 'Cantine';
      default:
        return 'Autre';
    }
  }

  void _showInvoiceDetails(Invoice invoice) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (context, scrollController) => SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Détails de la facture',
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                invoice.invoiceNumber,
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
              const Divider(height: 32),
              _buildDetailRow('Type', _getTypeLabel(invoice.type)),
              _buildDetailRow('Statut', _getStatusLabel(invoice.status)),
              _buildDetailRow('Montant total', '${invoice.amount.toStringAsFixed(2)} TND'),
              _buildDetailRow('Montant payé', '${invoice.paidAmount.toStringAsFixed(2)} TND'),
              _buildDetailRow('Montant restant', '${invoice.remainingAmount.toStringAsFixed(2)} TND'),
              _buildDetailRow('Date d\'échéance', DateFormat('dd/MM/yyyy').format(invoice.dueDate)),
              if (invoice.paidDate != null)
                _buildDetailRow('Date de paiement', DateFormat('dd/MM/yyyy').format(invoice.paidDate!)),
              if (invoice.description != null) ...[
                const SizedBox(height: 16),
                const Text('Description', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text(invoice.description!),
              ],
              if (invoice.payments.isNotEmpty) ...[
                const SizedBox(height: 24),
                const Text(
                  'Historique des paiements',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 12),
                ...invoice.payments.map((payment) => _buildPaymentTile(payment)).toList(),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600])),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentTile(Payment payment) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: const Icon(Icons.check_circle, color: Colors.green),
        title: Text('${payment.amount.toStringAsFixed(2)} TND'),
        subtitle: Text(
          '${DateFormat('dd/MM/yyyy').format(payment.paymentDate)} - ${_getPaymentMethodLabel(payment.paymentMethod)}',
        ),
        trailing: payment.hasReceipt
            ? IconButton(
                icon: const Icon(Icons.download),
                onPressed: () {
                  // Download receipt
                },
              )
            : null,
      ),
    );
  }

  String _getPaymentMethodLabel(String method) {
    switch (method) {
      case 'cash':
        return 'Espèces';
      case 'check':
        return 'Chèque';
      case 'bank_transfer':
        return 'Virement bancaire';
      case 'online':
        return 'En ligne';
      case 'card':
        return 'Carte bancaire';
      default:
        return method;
    }
  }

  Future<void> _initiatePayment(Invoice invoice) async {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Paiement en ligne'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Facture: ${invoice.invoiceNumber}'),
            const SizedBox(height: 8),
            Text('Montant à payer: ${invoice.remainingAmount.toStringAsFixed(2)} TND'),
            const SizedBox(height: 16),
            const Text('Méthode de paiement:'),
            const SizedBox(height: 8),
            ListTile(
              leading: const Icon(Icons.credit_card),
              title: const Text('Carte bancaire'),
              onTap: () {
                Navigator.pop(context);
                // Navigate to payment gateway
                _processPayment(invoice, 'card');
              },
            ),
            ListTile(
              leading: const Icon(Icons.account_balance),
              title: const Text('Virement bancaire'),
              onTap: () {
                Navigator.pop(context);
                _processPayment(invoice, 'bank_transfer');
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
        ],
      ),
    );
  }

  Future<void> _processPayment(Invoice invoice, String method) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );

    try {
      await _apiClient.makePayment(invoice.id, {
        'amount': invoice.remainingAmount,
        'payment_method': method,
      });

      Navigator.pop(context); // Close loading dialog

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Paiement effectué avec succès'),
          backgroundColor: Colors.green,
        ),
      );

      _loadPayments(); // Refresh data
    } catch (e) {
      Navigator.pop(context); // Close loading dialog

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur lors du paiement: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
