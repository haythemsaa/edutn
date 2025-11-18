class Invoice {
  final int id;
  final String invoiceNumber;
  final String type; // tuition, registration, transport, canteen, other
  final double amount;
  final double paidAmount;
  final double remainingAmount;
  final String status; // pending, partial, paid, overdue, cancelled
  final DateTime dueDate;
  final DateTime? paidDate;
  final String? description;
  final List<Payment> payments;
  final DateTime createdAt;

  Invoice({
    required this.id,
    required this.invoiceNumber,
    required this.type,
    required this.amount,
    required this.paidAmount,
    required this.remainingAmount,
    required this.status,
    required this.dueDate,
    this.paidDate,
    this.description,
    required this.payments,
    required this.createdAt,
  });

  factory Invoice.fromJson(Map<String, dynamic> json) {
    return Invoice(
      id: json['id'],
      invoiceNumber: json['invoice_number'] ?? '',
      type: json['type'] ?? 'other',
      amount: (json['amount'] ?? 0).toDouble(),
      paidAmount: (json['paid_amount'] ?? 0).toDouble(),
      remainingAmount: (json['remaining_amount'] ?? 0).toDouble(),
      status: json['status'] ?? 'pending',
      dueDate: DateTime.parse(json['due_date']),
      paidDate: json['paid_date'] != null ? DateTime.parse(json['paid_date']) : null,
      description: json['description'],
      payments: (json['payments'] as List? ?? [])
          .map((p) => Payment.fromJson(p))
          .toList(),
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  bool get isPaid => status == 'paid';
  bool get isPending => status == 'pending';
  bool get isOverdue => status == 'overdue';
  bool get isPartiallyPaid => status == 'partial';
  bool get hasDueDatePassed => DateTime.now().isAfter(dueDate) && !isPaid;

  int get daysUntilDue => dueDate.difference(DateTime.now()).inDays;
  int get daysOverdue => DateTime.now().difference(dueDate).inDays;
}

class Payment {
  final int id;
  final int invoiceId;
  final double amount;
  final String paymentMethod; // cash, check, bank_transfer, online, card
  final String? transactionId;
  final String? receiptPath;
  final DateTime paymentDate;
  final String? notes;
  final DateTime createdAt;

  Payment({
    required this.id,
    required this.invoiceId,
    required this.amount,
    required this.paymentMethod,
    this.transactionId,
    this.receiptPath,
    required this.paymentDate,
    this.notes,
    required this.createdAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'],
      invoiceId: json['invoice_id'],
      amount: (json['amount'] ?? 0).toDouble(),
      paymentMethod: json['payment_method'] ?? 'cash',
      transactionId: json['transaction_id'],
      receiptPath: json['receipt_path'],
      paymentDate: DateTime.parse(json['payment_date']),
      notes: json['notes'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  bool get hasReceipt => receiptPath != null;
}

class PaymentStats {
  final double totalAmount;
  final double paidAmount;
  final double pendingAmount;
  final double overdueAmount;
  final int totalInvoices;
  final int paidInvoices;
  final int pendingInvoices;
  final int overdueInvoices;

  PaymentStats({
    required this.totalAmount,
    required this.paidAmount,
    required this.pendingAmount,
    required this.overdueAmount,
    required this.totalInvoices,
    required this.paidInvoices,
    required this.pendingInvoices,
    required this.overdueInvoices,
  });

  factory PaymentStats.fromJson(Map<String, dynamic> json) {
    return PaymentStats(
      totalAmount: (json['total_amount'] ?? 0).toDouble(),
      paidAmount: (json['paid_amount'] ?? 0).toDouble(),
      pendingAmount: (json['pending_amount'] ?? 0).toDouble(),
      overdueAmount: (json['overdue_amount'] ?? 0).toDouble(),
      totalInvoices: json['total_invoices'] ?? 0,
      paidInvoices: json['paid_invoices'] ?? 0,
      pendingInvoices: json['pending_invoices'] ?? 0,
      overdueInvoices: json['overdue_invoices'] ?? 0,
    );
  }
}
