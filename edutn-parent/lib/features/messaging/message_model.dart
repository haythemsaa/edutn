class Conversation {
  final int id;
  final String title;
  final String? lastMessage;
  final DateTime? lastMessageAt;
  final int unreadCount;
  final List<Participant> participants;

  Conversation({
    required this.id,
    required this.title,
    this.lastMessage,
    this.lastMessageAt,
    required this.unreadCount,
    required this.participants,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      id: json['id'],
      title: json['title'],
      lastMessage: json['last_message'],
      lastMessageAt: json['last_message_at'] != null
          ? DateTime.parse(json['last_message_at'])
          : null,
      unreadCount: json['unread_count'] ?? 0,
      participants: (json['participants'] as List?)
              ?.map((p) => Participant.fromJson(p))
              .toList() ??
          [],
    );
  }
}

class Participant {
  final int id;
  final String name;
  final String role;
  final String? avatar;

  Participant({
    required this.id,
    required this.name,
    required this.role,
    this.avatar,
  });

  factory Participant.fromJson(Map<String, dynamic> json) {
    return Participant(
      id: json['id'],
      name: json['name'],
      role: json['role'],
      avatar: json['avatar'],
    );
  }
}

class Message {
  final int id;
  final int conversationId;
  final int senderId;
  final String senderName;
  final String senderRole;
  final String content;
  final String? attachmentUrl;
  final DateTime createdAt;
  final bool isRead;
  final bool isMine;

  Message({
    required this.id,
    required this.conversationId,
    required this.senderId,
    required this.senderName,
    required this.senderRole,
    required this.content,
    this.attachmentUrl,
    required this.createdAt,
    required this.isRead,
    required this.isMine,
  });

  factory Message.fromJson(Map<String, dynamic> json, int currentUserId) {
    return Message(
      id: json['id'],
      conversationId: json['conversation_id'],
      senderId: json['sender_id'],
      senderName: json['sender_name'],
      senderRole: json['sender_role'],
      content: json['content'],
      attachmentUrl: json['attachment_url'],
      createdAt: DateTime.parse(json['created_at']),
      isRead: json['is_read'] ?? false,
      isMine: json['sender_id'] == currentUserId,
    );
  }
}
