import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';
import 'message_model.dart';
import 'chat_screen.dart';

class MessagingScreen extends StatefulWidget {
  const MessagingScreen({Key? key}) : super(key: key);

  @override
  State<MessagingScreen> createState() => _MessagingScreenState();
}

class _MessagingScreenState extends State<MessagingScreen> {
  final ApiClient _apiClient = ApiClient();
  List<Conversation> _conversations = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadConversations();
  }

  Future<void> _loadConversations() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getConversations();
      setState(() {
        _conversations = data.map((json) => Conversation.fromJson(json)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadConversations,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : _conversations.isEmpty
                  ? _buildEmptyView()
                  : RefreshIndicator(
                      onRefresh: _loadConversations,
                      child: ListView.separated(
                        itemCount: _conversations.length,
                        separatorBuilder: (context, index) => const Divider(height: 1),
                        itemBuilder: (context, index) {
                          return _buildConversationTile(_conversations[index]);
                        },
                      ),
                    ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {},
        child: const Icon(Icons.add),
        tooltip: 'Nouvelle conversation',
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
            onPressed: _loadConversations,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'Aucun message',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'Vos conversations apparaîtront ici',
            style: TextStyle(color: Colors.grey[500]),
          ),
        ],
      ),
    );
  }

  Widget _buildConversationTile(Conversation conversation) {
    final hasUnread = conversation.unreadCount > 0;

    return ListTile(
      leading: CircleAvatar(
        backgroundColor: hasUnread ? Colors.blue : Colors.grey[300],
        child: Icon(
          Icons.person,
          color: hasUnread ? Colors.white : Colors.grey[600],
        ),
      ),
      title: Row(
        children: [
          Expanded(
            child: Text(
              conversation.title,
              style: TextStyle(
                fontWeight: hasUnread ? FontWeight.bold : FontWeight.normal,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (hasUnread)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.blue,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                conversation.unreadCount.toString(),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
        ],
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (conversation.lastMessage != null) ...[
            const SizedBox(height: 4),
            Text(
              conversation.lastMessage!,
              style: TextStyle(
                color: hasUnread ? Colors.black87 : Colors.grey[600],
                fontWeight: hasUnread ? FontWeight.w500 : FontWeight.normal,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
      trailing: conversation.lastMessageAt != null
          ? Text(
              _formatTime(conversation.lastMessageAt!),
              style: TextStyle(
                fontSize: 12,
                color: hasUnread ? Colors.blue : Colors.grey[500],
                fontWeight: hasUnread ? FontWeight.bold : FontWeight.normal,
              ),
            )
          : null,
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ChatScreen(conversation: conversation),
          ),
        ).then((_) => _loadConversations());
      },
    );
  }

  String _formatTime(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays == 0) {
      return DateFormat('HH:mm').format(dateTime);
    } else if (difference.inDays == 1) {
      return 'Hier';
    } else if (difference.inDays < 7) {
      return DateFormat('EEEE', 'fr').format(dateTime);
    } else {
      return DateFormat('dd/MM').format(dateTime);
    }
  }
}
