<template>
    <div class="chat-container">
        <div class="chat-layout">
            <!-- قائمة المحادثات -->
            <div class="conversations-sidebar">
                <div class="sidebar-header">
                    <h3>المحادثات</h3>
                    <button class="btn-new-chat" @click="startNewChat">
                        <i class="plus-icon"></i>
                        محادثة جديدة
                    </button>
                </div>
                
                <div class="search-box">
                    <input type="text" v-model="searchTerm" placeholder="ابحث في المحادثات..." class="search-input">
                </div>
                
                <div class="conversations-list">
                    <div v-for="conversation in filteredConversations" 
                         :key="conversation.id" 
                         class="conversation-item"
                         :class="{ active: activeConversation?.id === conversation.id }"
                         @click="selectConversation(conversation)">
                        <div class="conversation-avatar">
                            <img :src="getConversationAvatar(conversation)" :alt="conversation.name">
                            <span class="online-indicator" v-if="conversation.isOnline"></span>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <h4>{{ conversation.name }}</h4>
                                <span class="time">{{ formatTime(conversation.lastMessageTime) }}</span>
                            </div>
                            <p class="last-message">{{ conversation.lastMessage }}</p>
                            <div class="conversation-meta">
                                <span class="unread-count" v-if="conversation.unreadCount > 0">
                                    {{ conversation.unreadCount }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- نافذة المحادثة -->
            <div class="chat-main">
                <div v-if="activeConversation" class="chat-window">
                    <div class="chat-header">
                        <div class="chat-partner-info">
                            <div class="partner-avatar">
                                <img :src="getConversationAvatar(activeConversation)" :alt="activeConversation.name">
                                <span class="online-indicator" v-if="activeConversation.isOnline"></span>
                            </div>
                            <div class="partner-details">
                                <h3>{{ activeConversation.name }}</h3>
                                <p class="partner-status">{{ activeConversation.isOnline ? 'متصل الآن' : 'غير متصل' }}</p>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn-icon" title="مكالمة صوتية">
                                <i class="call-icon"></i>
                            </button>
                            <button class="btn-icon" title="مكالمة فيديو">
                                <i class="video-icon"></i>
                            </button>
                            <button class="btn-icon" title="معلومات المحادثة">
                                <i class="info-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="messages-container" ref="messagesContainer">
                        <div class="messages-list">
                            <div v-for="message in activeConversation.messages" 
                                 :key="message.id" 
                                 class="message"
                                 :class="{
                                     'sent': message.sender === 'me',
                                     'received': message.sender !== 'me'
                                 }">
                                <div class="message-content">
                                    <p>{{ message.text }}</p>
                                    <span class="message-time">{{ formatMessageTime(message.timestamp) }}</span>
                                </div>
                                <div class="message-status" v-if="message.sender === 'me'">
                                    <i v-if="message.status === 'sent'" class="sent-icon"></i>
                                    <i v-if="message.status === 'delivered'" class="delivered-icon"></i>
                                    <i v-if="message.status === 'read'" class="read-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="message-input-container">
                        <div class="input-actions">
                            <button class="btn-icon" title="إرفاق ملف">
                                <i class="attach-icon"></i>
                            </button>
                            <button class="btn-icon" title="إرسال صورة">
                                <i class="image-icon"></i>
                            </button>
                            <button class="btn-icon" title="إرسال إيموجي">
                                <i class="emoji-icon"></i>
                            </button>
                        </div>
                        <textarea v-model="newMessage" 
                                  @keypress.enter.prevent="sendMessage"
                                  placeholder="اكتب رسالتك هنا..."
                                  class="message-input"
                                  rows="1"></textarea>
                        <button class="btn-send" @click="sendMessage" :disabled="!newMessage.trim()">
                            <i class="send-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- حالة عدم وجود محادثة محددة -->
                <div v-else class="no-chat-selected">
                    <div class="welcome-message">
                        <i class="chat-icon"></i>
                        <h3>مرحباً في نظام المحادثات</h3>
                        <p>اختر محادثة من القائمة لبدء المحادثة أو ابدأ محادثة جديدة</p>
                        <button class="btn-primary" @click="startNewChat">
                            بدء محادثة جديدة
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- مودال بدء محادثة جديدة -->
        <div v-if="showNewChatModal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3>بدء محادثة جديدة</h3>
                    <button class="close-btn" @click="closeNewChatModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="user-search">
                        <input type="text" v-model="userSearch" placeholder="ابحث عن مستخدم..." class="search-input">
                    </div>
                    <div class="users-list">
                        <div v-for="user in filteredUsers" 
                             :key="user.id" 
                             class="user-item"
                             @click="selectUser(user)">
                            <div class="user-avatar">
                                <img :src="user.avatar" :alt="user.name">
                                <span class="online-indicator" v-if="user.isOnline"></span>
                            </div>
                            <div class="user-info">
                                <h4>{{ user.name }}</h4>
                                <p>{{ user.department }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'

export default {
    name: 'ChatContainer',
    setup() {
        // البيانات التفاعلية
        const activeConversation = ref(null)
        const showNewChatModal = ref(false)
        const searchTerm = ref('')
        const userSearch = ref('')
        const newMessage = ref('')
        const messagesContainer = ref(null)

        // بيانات المحادثات (وهمية للعرض)
        const conversations = ref([
            {
                id: 1,
                name: 'أحمد محمد',
                type: 'private',
                avatar: '/images/avatars/1.jpg',
                isOnline: true,
                lastMessage: 'شكراً على المساعدة في المشروع',
                lastMessageTime: '2024-01-15T10:30:00',
                unreadCount: 2,
                messages: [
                    {
                        id: 1,
                        text: 'مرحباً، كيف يمكنني مساعدتك؟',
                        sender: 'them',
                        timestamp: '2024-01-15T10:00:00',
                        status: 'read'
                    },
                    {
                        id: 2,
                        text: 'أحتاج مساعدة في تقرير المبيعات',
                        sender: 'me',
                        timestamp: '2024-01-15T10:15:00',
                        status: 'read'
                    },
                    {
                        id: 3,
                        text: 'بالطبع، سأرسل لك البيانات المطلوبة',
                        sender: 'them',
                        timestamp: '2024-01-15T10:20:00',
                        status: 'read'
                    },
                    {
                        id: 4,
                        text: 'شكراً على المساعدة في المشروع',
                        sender: 'them',
                        timestamp: '2024-01-15T10:30:00',
                        status: 'delivered'
                    }
                ]
            },
            {
                id: 2,
                name: 'فريق المبيعات',
                type: 'group',
                avatar: '/images/group-avatar.png',
                isOnline: false,
                lastMessage: 'علي: الاجتماع سيبدأ بعد ساعة',
                lastMessageTime: '2024-01-15T09:45:00',
                unreadCount: 0,
                messages: []
            },
            {
                id: 3,
                name: 'فاطمة أحمد',
                type: 'private',
                avatar: '/images/avatars/2.jpg',
                isOnline: true,
                lastMessage: 'هل انتهيت من التقرير؟',
                lastMessageTime: '2024-01-14T16:20:00',
                unreadCount: 1,
                messages: []
            }
        ])

        // قائمة المستخدمين (للمحادثات الجديدة)
        const users = ref([
            {
                id: 1,
                name: 'أحمد محمد',
                department: 'تقنية المعلومات',
                avatar: '/images/avatars/1.jpg',
                isOnline: true
            },
            {
                id: 2,
                name: 'فاطمة أحمد',
                department: 'الموارد البشرية',
                avatar: '/images/avatars/2.jpg',
                isOnline: true
            },
            {
                id: 3,
                name: 'خالد عبدالله',
                department: 'المبيعات',
                avatar: '/images/avatars/3.jpg',
                isOnline: false
            }
        ])

        // الحسابات
        const filteredConversations = computed(() => {
            if (!searchTerm.value) return conversations.value
            return conversations.value.filter(conv => 
                conv.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                conv.lastMessage.toLowerCase().includes(searchTerm.value.toLowerCase())
            )
        })

        const filteredUsers = computed(() => {
            if (!userSearch.value) return users.value
            return users.value.filter(user => 
                user.name.toLowerCase().includes(userSearch.value.toLowerCase()) ||
                user.department.toLowerCase().includes(userSearch.value.toLowerCase())
            )
        })

        // الدوال
        const selectConversation = (conversation) => {
            activeConversation.value = conversation
            // reset unread count
            conversation.unreadCount = 0
        }

        const startNewChat = () => {
            showNewChatModal.value = true
        }

        const closeNewChatModal = () => {
            showNewChatModal.value = false
            userSearch.value = ''
        }

        const selectUser = (user) => {
            // إنشاء محادثة جديدة
            const newConversation = {
                id: Date.now(),
                name: user.name,
                type: 'private',
                avatar: user.avatar,
                isOnline: user.isOnline,
                lastMessage: '',
                lastMessageTime: new Date().toISOString(),
                unreadCount: 0,
                messages: []
            }
            
            conversations.value.unshift(newConversation)
            activeConversation.value = newConversation
            closeNewChatModal()
        }

        const sendMessage = async () => {
            if (!newMessage.value.trim() || !activeConversation.value) return

            const message = {
                id: Date.now(),
                text: newMessage.value.trim(),
                sender: 'me',
                timestamp: new Date().toISOString(),
                status: 'sent'
            }

            // إضافة الرسالة للمحادثة النشطة
            activeConversation.value.messages.push(message)
            activeConversation.value.lastMessage = message.text
            activeConversation.value.lastMessageTime = message.timestamp

            // محاكاة الرد
            setTimeout(() => {
                const reply = {
                    id: Date.now() + 1,
                    text: 'شكراً على رسالتك، سأرد عليك قريباً',
                    sender: 'them',
                    timestamp: new Date().toISOString(),
                    status: 'read'
                }
                activeConversation.value.messages.push(reply)
                activeConversation.value.lastMessage = reply.text
                activeConversation.value.lastMessageTime = reply.timestamp
                scrollToBottom()
            }, 1000)

            newMessage.value = ''
            await nextTick()
            scrollToBottom()
        }

        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
            }
        }

        const getConversationAvatar = (conversation) => {
            return conversation.avatar || '/images/default-avatar.png'
        }

        const formatTime = (timeString) => {
            const date = new Date(timeString)
            const now = new Date()
            const diff = now - date
            
            if (diff < 24 * 60 * 60 * 1000) {
                return date.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' })
            } else {
                return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' })
            }
        }

        const formatMessageTime = (timeString) => {
            return new Date(timeString).toLocaleTimeString('ar-EG', { 
                hour: '2-digit', 
                minute: '2-digit' 
            })
        }

        // دورة الحياة
        onMounted(() => {
            // تحميل المحادثات من API
            setTimeout(() => {
                scrollToBottom()
            }, 100)
        })

        return {
            activeConversation,
            showNewChatModal,
            searchTerm,
            userSearch,
            newMessage,
            messagesContainer,
            conversations,
            users,
            filteredConversations,
            filteredUsers,
            selectConversation,
            startNewChat,
            closeNewChatModal,
            selectUser,
            sendMessage,
            getConversationAvatar,
            formatTime,
            formatMessageTime
        }
    }
}
</script>

<style scoped>
.chat-container {
    height: 100vh;
    background: #f8f9fa;
}

.chat-layout {
    display: flex;
    height: 100%;
}

/* الشريط الجانبي للمحادثات */
.conversations-sidebar {
    width: 350px;
    background: white;
    border-left: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h3 {
    margin: 0;
    color: #2c3e50;
}

.btn-new-chat {
    background: #3498db;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.search-box {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.search-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
}

.search-input:focus {
    border-color: #3498db;
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background: #f8f9fa;
}

.conversation-item.active {
    background: #e3f2fd;
    border-right: 3px solid #3498db;
}

.conversation-avatar {
    position: relative;
}

.conversation-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #27ae60;
    border: 2px solid white;
    border-radius: 50%;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.conversation-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 14px;
}

.time {
    font-size: 12px;
    color: #7f8c8d;
}

.last-message {
    margin: 0;
    color: #7f8c8d;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-meta {
    display: flex;
    justify-content: flex-end;
    margin-top: 5px;
}

.unread-count {
    background: #e74c3c;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

/* المنطقة الرئيسية للمحادثة */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-window {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
}

.no-chat-selected {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
}

.welcome-message {
    text-align: center;
    color: #7f8c8d;
}

.welcome-message .chat-icon {
    font-size: 64px;
    margin-bottom: 20px;
    color: #bdc3c7;
}

.welcome-message h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.welcome-message p {
    margin: 0 0 20px 0;
}

/* رأس المحادثة */
.chat-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.chat-partner-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.partner-avatar {
    position: relative;
}

.partner-avatar img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.partner-details h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.partner-status {
    margin: 0;
    color: #27ae60;
    font-size: 13px;
}

.chat-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    background: none;
    border: none;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
    color: #7f8c8d;
    transition: background-color 0.2s;
}

.btn-icon:hover {
    background: #f8f9fa;
    color: #3498db;
}

/* منطقة الرسائل */
.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f0f2f5;
}

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 70%;
}

.message.sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message.received {
    align-self: flex-start;
}

.message-content {
    background: white;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    position: relative;
}

.message.sent .message-content {
    background: #3498db;
    color: white;
}

.message-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
}

.message-time {
    font-size: 11px;
    color: #7f8c8d;
    margin-top: 5px;
    display: block;
}

.message.sent .message-time {
    color: rgba(255,255,255,0.8);
}

.message-status {
    font-size: 14px;
    color: #7f8c8d;
}

.message.sent .message-status {
    color: #3498db;
}

/* منطقة إدخال الرسالة */
.message-input-container {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    background: white;
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.input-actions {
    display: flex;
    gap: 5px;
}

.message-input {
    flex: 1;
    border: none;
    outline: none;
    resize: none;
    font-size: 14px;
    font-family: inherit;
    line-height: 1.4;
    max-height: 120px;
    padding: 10px 0;
}

.message-input::placeholder {
    color: #bdc3c7;
}

.btn-send {
    background: #3498db;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.btn-send:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.btn-send:hover:not(:disabled) {
    background: #2980b9;
}

/* المودال */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    max-height: 80vh;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #7f8c8d;
}

.modal-body {
    padding: 20px;
}

.user-search {
    margin-bottom: 15px;
}

.users-list {
    max-height: 300px;
    overflow-y: auto;
}

.user-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: background-color 0.2s;
}

.user-item:hover {
    background: #f8f9fa;
}

.user-item:last-child {
    border-bottom: none;
}

.user-avatar {
    position: relative;
}

.user-avatar img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 14px;
}

.user-info p {
    margin: 0;
    color: #7f8c8d;
    font-size: 12px;
}

/* الأيقونات (يمكن استبدالها بأيقونات حقيقية) */
.plus-icon::before { content: '+'; }
.call-icon::before { content: '📞'; }
.video-icon::before { content: '📹'; }
.info-icon::before { content: 'ℹ️'; }
.attach-icon::before { content: '📎'; }
.image-icon::before { content: '🖼️'; }
.emoji-icon::before { content: '😊'; }
.send-icon::before { content: '➤'; }
.sent-icon::before { content: '✓'; }
.delivered-icon::before { content: '✓✓'; }
.read-icon::before { content: '✓✓'; color: #3498db; }
.chat-icon::before { content: '💬'; }

@media (max-width: 768px) {
    .conversations-sidebar {
        width: 100%;
        display: none;
    }
    
    .conversations-sidebar.mobile-visible {
        display: flex;
    }
    
    .chat-main {
        width: 100%;
    }
    
    .message {
        max-width: 85%;
    }
}

/* تخصيص شريط التمرير */
.conversations-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar,
.users-list::-webkit-scrollbar {
    width: 6px;
}

.conversations-list::-webkit-scrollbar-track,
.messages-container::-webkit-scrollbar-track,
.users-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.conversations-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb,
.users-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.conversations-list::-webkit-scrollbar-thumb:hover,
.messages-container::-webkit-scrollbar-thumb:hover,
.users-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
