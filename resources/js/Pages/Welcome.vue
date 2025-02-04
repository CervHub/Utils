<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_KEY = 'your-api-key-here'; // Reemplaza con tu API key
const messages = ref([
    { text: 'Hola, ¿cómo puedo ayudarte hoy?', sender: 'bot' }
]);
const newMessage = ref('');
const chatContainer = ref(null);
const isTyping = ref(false);
const isSending = ref(false);
const showAssistantsModal = ref(false);
const assistants = ref([]);
const threads = ref([]);
const activeTab = ref('chat');
const threadId = ref(localStorage.getItem('thread_id') || null);

async function createThread() {
    try {
        const response = await axios.post(route('openai.createThread'), {
            apiKey: API_KEY,
            userId: 'optional-user-id' // Reemplaza con el ID del usuario si es necesario
        });
        threadId.value = response.data.thread.thread_id;
        localStorage.setItem('thread_id', threadId.value);
        console.log('Thread creado:', threadId.value);
    } catch (error) {
        console.error('Error creando el thread:', error);
    }
}

async function sendMessage() {
    if (newMessage.value.trim() === '' || isSending.value) return;

    if (!threadId.value) {
        await createThread();
    }

    const userMessage = newMessage.value;
    messages.value.push({ text: userMessage, sender: 'user' });
    newMessage.value = '';
    isSending.value = true;
    isTyping.value = true;

    const useAssistant = true; // Cambia esto a false si quieres usar openai.message

    try {
        let response;
        if (useAssistant) {
            response = await axios.post(route('openai.assistant'), {
                message: userMessage,
                thread_id: threadId.value
            });

            // Obtener el mensaje directamente
            const content = response.data.message;

            if (content) {
                messages.value.push({ text: content, sender: 'bot' });
            } else {
                messages.value.push({ text: 'Error: No se pudo obtener la respuesta completa.', sender: 'bot' });
            }
        } else {
            response = await axios.post(route('openai.message'), {
                message: userMessage
            });

            messages.value.push({ text: response.data.message, sender: 'bot' });
        }
    } catch (error) {
        messages.value.push({ text: 'Error: No se pudo enviar el mensaje.', sender: 'bot' });
    } finally {
        isSending.value = false;
        isTyping.value = false;
    }

    scrollToBottom();
}

async function fetchAssistants() {
    try {
        const response = await axios.get(route('openai.assistants'));
        assistants.value = response.data.assistants.data;
        showAssistantsModal.value = true;
    } catch (error) {
        console.error('Error fetching assistants:', error);
    }
}

async function fetchThreads() {
    try {
        const response = await axios.get(route('openai.listThreads'));
        threads.value = response.data.threads;
    } catch (error) {
        console.error('Error fetching threads:', error);
    }
}

function scrollToBottom() {
    setTimeout(() => {
        chatContainer.value?.scrollTo({ top: chatContainer.value.scrollHeight, behavior: 'smooth' });
    }, 100);
}

onMounted(async () => {
    if (!threadId.value || threadId.value === 'null' || threadId.value === 'undefined') {
        await createThread();
    }
    console.log('Thread ID:', threadId.value);
    console.log('API Key:', API_KEY);
    scrollToBottom();
});
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="container h-full mx-auto p-6 bg-white rounded-lg shadow-lg">
            <div class="md:flex items-center h-full">
                <ul
                    class="flex flex-col justify-center space-y-4 text-sm font-medium text-gray-500 md:me-4 mb-4 md:mb-0">
                    <li>
                        <a href="#" @click.prevent="activeTab = 'chat'"
                            :class="{ 'bg-blue-700 text-white': activeTab === 'chat', 'bg-gray-50 text-gray-900': activeTab !== 'chat' }"
                            class="inline-flex items-center px-4 py-3 rounded-lg w-full">
                            <svg class="w-4 h-4 me-2 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                            </svg>
                            Chat
                        </a>
                    </li>
                    <li>
                        <a href="#" @click.prevent="activeTab = 'assistants'; fetchAssistants()"
                            :class="{ 'bg-blue-700 text-white': activeTab === 'assistants', 'bg-gray-50 text-gray-900': activeTab !== 'assistants' }"
                            class="inline-flex items-center px-4 py-3 rounded-lg w-full">
                            <svg class="w-4 h-4 me-2 text-gray-500" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M18 7.5h-.423l-.452-1.09.3-.3a1.5 1.5 0 0 0 0-2.121L16.01 2.575a1.5 1.5 0 0 0-2.121 0l-.3.3-1.089-.452V2A1.5 1.5 0 0 0 11 .5H9A1.5 1.5 0 0 0 7.5 2v.423l-1.09.452-.3-.3a1.5 1.5 0 0 0-2.121 0L2.576 3.99a1.5 1.5 0 0 0 0 2.121l.3.3L2.423 7.5H2A1.5 1.5 0 0 0 .5 9v2A1.5 1.5 0 0 0 2 12.5h.423l.452 1.09-.3.3a1.5 1.5 0 0 0 0 2.121l1.415 1.413a1.5 1.5 0 0 0 2.121 0l.3-.3 1.09.452V18A1.5 1.5 0 0 0 9 19.5h2a1.5 1.5 0 0 0 1.5-1.5v-.423l1.09-.452.3.3a1.5 1.5 0 0 0 2.121 0l1.415-1.414a1.5 1.5 0 0 0 0-2.121l-.3-.3.452-1.09H18a1.5 1.5 0 0 0 1.5-1.5V9A1.5 1.5 0 0 0 18 7.5Zm-8 6a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Z" />
                            </svg>
                            Asistentes
                        </a>
                    </li>
                    <li>
                        <a href="#" @click.prevent="activeTab = 'threads'; fetchThreads()"
                            :class="{ 'bg-blue-700 text-white': activeTab === 'threads', 'bg-gray-50 text-gray-900': activeTab !== 'threads' }"
                            class="inline-flex items-center px-4 py-3 rounded-lg w-full">
                            <svg class="w-4 h-4 me-2 text-gray-500" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                            </svg>
                            Threads
                        </a>
                    </li>
                </ul>
                <div
                    class="flex flex-col justify-center p-6 bg-gray-50 text-medium text-gray-500 rounded-lg w-full h-full">
                    <div v-if="activeTab === 'chat'">
                        <div ref="chatContainer"
                            class="messages-container flex-1 w-full overflow-y-auto bg-white p-4 rounded-lg shadow-md mb-4 h-full">
                            <div v-for="(message, index) in messages" :key="index"
                                :class="{ 'text-right': message.sender === 'user', 'text-left': message.sender === 'bot' }">
                                <div :class="{ 'bg-blue-500 text-white': message.sender === 'user', 'bg-gray-300 text-black': message.sender === 'bot' }"
                                    class="inline-block p-2 rounded-lg mb-2" v-html="message.text">
                                </div>
                            </div>
                            <div v-if="isTyping" class="text-left">
                                <div class="bg-gray-300 text-black inline-block p-2 rounded-lg mb-2">
                                    Escribiendo...
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex justify-between items-center">
                            <input v-model="newMessage" @keyup.enter="sendMessage" type="text"
                                class="message-input w-full p-2 border rounded-lg"
                                placeholder="Escribe tu mensaje aquí..." :disabled="isSending">
                            <button @click="sendMessage"
                                class="ml-2 p-2 bg-blue-500 text-white rounded-lg">Enviar</button>
                        </div>
                    </div>
                    <div v-if="activeTab === 'assistants'">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-full">
                            <h2 class="text-2xl font-bold mb-4 text-center">Lista de Asistentes</h2>
                            <ul class="space-y-4">
                                <li v-for="(assistant, index) in assistants" :key="index"
                                    class="bg-gray-100 p-4 rounded-lg shadow-md">
                                    <details class="group">
                                        <summary
                                            class="cursor-pointer text-lg font-semibold text-blue-600 group-open:text-blue-800">
                                            {{ assistant.id }}
                                        </summary>
                                        <div class="mt-2 space-y-2">
                                            <p><strong>Nombre:</strong> {{ assistant.name }}</p>
                                            <p><strong>Descripción:</strong> {{ assistant.description }}</p>
                                            <p><strong>Modelo:</strong> {{ assistant.model }}</p>
                                            <p><strong>Instrucciones:</strong> {{ assistant.instructions }}</p>
                                            <p><strong>Top P:</strong> {{ assistant.top_p }}</p>
                                            <p><strong>Temperatura:</strong> {{ assistant.temperature }}</p>
                                            <p><strong>Formato de Respuesta:</strong> {{ assistant.response_format }}
                                            </p>
                                        </div>
                                    </details>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-if="activeTab === 'threads'">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-full">
                            <h2 class="text-2xl font-bold mb-4 text-center">Lista de Threads</h2>
                            <ul class="space-y-4">
                                <li v-for="(thread, index) in threads" :key="index"
                                    class="bg-gray-100 p-4 rounded-lg shadow-md">
                                    <details class="group">
                                        <summary
                                            class="cursor-pointer text-lg font-semibold text-blue-600 group-open:text-blue-800">
                                            {{ thread.thread_id }}
                                        </summary>
                                        <div class="mt-2 space-y-2">
                                            <p><strong>API Key:</strong> {{ thread.apikey }}</p>
                                            <p><strong>Status:</strong> {{ thread.status }}</p>
                                            <p><strong>Expires At:</strong> {{ thread.expires_at }}</p>
                                        </div>
                                    </details>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
