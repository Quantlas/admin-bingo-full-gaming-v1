<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Modal } from 'flowbite';
import { BanIcon, EyeIcon, HandCoins, Search, Trophy } from 'lucide-vue-next';
import { defineProps, ref, type PropType } from 'vue';

const props = defineProps({
    players: {
        type: Array as PropType<Player[]>,
        required: true,
    },
});

console.log('Jugadores:', props.players);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Jugadores',
        href: '/dashboard/players',
    },
];

interface CardNumbers {
    B: number[];
    I: number[];
    N: (number | null)[];
    G: number[];
    O: number[];
}

interface Player {
    id: number;
    name: string;
    game_name: string;
    serial_number: string;
    payment_reference: string;
    numbers: string;
    card_path: string;
    status: string;
    winner: boolean;
    created_at: string;
}

const showNew = ref(false);

const carton = ref({
    name: '',
    game_name: '',
    serial_number: '',
    payment_reference: '',
    numbers: '',
    card_path: '',
    status: '',
    winner: false,
    created_at: '',
});

const updateCard = (card_id: number, status: string) => {
    axios
        .put('/players/' + card_id, {
            status: status,
        })
        .then((response) => {
            console.log(response);
        })
        .catch((error) => {
            console.error(error);
        })
        .finally(() => {
            router.reload();
            console.log('finally');
        });
};

const resolveStatus = (status: string) => {
    switch (status) {
        case 'pending':
            return 'Pendiente';
        case 'paid':
            return 'Pagado';
        case 'cancelled':
            return 'Cancelado';
        default:
            return 'Desconocido';
    }
};

function openModal(player: Player) {
    carton.value = player;
    processCardData(player.numbers); // Pasa la cadena JSON directamente

    const modalElement = document.getElementById('popup-modal');
    if (modalElement) {
        const modalInstance = new Modal(modalElement);
        modalInstance.show();
    }
}

const closeModal = () => {
    carton.value = {
        name: '',
        game_name: '',
        serial_number: '',
        payment_reference: '',
        numbers: '',
        card_path: '',
        status: '',
        winner: false,
        created_at: '',
    };
    const modalElement = document.getElementById('popup-modal');
    if (modalElement) {
        // Crear una instancia del modal
        const modalInstance = new Modal(modalElement);
        // Cerrar el modal
        modalInstance.hide();
    }
};

const markedNumbers = ref<number[]>([]);
const serialNumber = ref('');
const cardNumbers = ref<(number | null)[][]>([]);

const processCardData = (jsonString: string) => {
    try {
        // Parsea la cadena JSON a objeto
        const data = JSON.parse(jsonString) as CardNumbers;
        console.log('Datos del cartón:', data);

        // Extrae el serial number del objeto carton.value
        serialNumber.value = carton.value.serial_number;

        // Construye la matriz del cartón
        cardNumbers.value = [data.B || [], data.I || [], data.N || [], data.G || [], data.O || []];

        console.log('Cartón procesado:', cardNumbers.value);
    } catch (error) {
        console.error('Error al procesar el cartón:', error);
        // Puedes inicializar con un cartón vacío en caso de error
        cardNumbers.value = Array(5).fill(Array(5).fill(null));
    }
};

const formatDate = (isoString: string): string => {
    const date = new Date(isoString);

    const pad = (num: number) => num.toString().padStart(2, '0');

    const day = pad(date.getDate());
    const month = pad(date.getMonth() + 1);
    const year = date.getFullYear();
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    const seconds = pad(date.getSeconds());

    return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
};

// Crear un estado local para los jugadores
const localPlayers = ref([...props.players]);

const searchQuery = ref('');
const buscarJugadores = () => {
    if (searchQuery.value.length > 0) {
        axios
            .get('/players/search?q=' + searchQuery.value)
            .then((response) => {
                console.log(response.data);
                localPlayers.value = response.data;
            })
            .catch((error) => {
                console.error(error);
            });
    } else {
        router.reload();
    }
};
</script>

<template>
    <Head title="Jugadores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-12 flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400 rtl:text-right">
                    <caption class="bg-white p-5 text-left text-lg font-semibold text-gray-900 dark:bg-gray-800 dark:text-white rtl:text-right">
                        Jugadores
                        <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">Aquí puede ver todos los jugadores.</p>

                        <!-- barra de búsqueda -->
                        <div class="relative mt-4 w-[400px]">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @keyup.enter="buscarJugadores"
                                placeholder="Buscar jugadores..."
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pr-12 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            />
                            <button @click="buscarJugadores" class="absolute right-2 top-1/2 -translate-y-1/3 text-gray-500 hover:text-blue-500">
                                <component :is="Search" />
                            </button>
                        </div>
                    </caption>

                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nombre</th>
                            <th scope="col" class="px-6 py-3">Serial</th>
                            <th scope="col" class="px-6 py-3">Referencia</th>
                            <th scope="col" class="px-6 py-3">Estatus</th>
                            <th scope="col" class="px-6 py-3">Fecha</th>
                            <th scope="col" class="px-6 py-3">Ganador</th>
                            <th scope="col" class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="player in localPlayers"
                            class="border-b border-gray-200 odd:bg-white even:bg-gray-50 dark:border-gray-700 odd:dark:bg-gray-900 even:dark:bg-gray-800"
                        >
                            <th scope="row" class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ player.name }}
                            </th>
                            <td class="px-6 py-4">{{ player.serial_number }}</td>
                            <td class="px-6 py-4">{{ player.payment_reference }}</td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="player.status === 'pending'"
                                    class="me-2 rounded-sm bg-yellow-100 px-2.5 py-0.5 text-sm font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                >
                                    {{ resolveStatus(player.status) }}
                                </span>
                                <span
                                    v-if="player.status === 'paid'"
                                    class="me-2 rounded-sm bg-blue-100 px-2.5 py-0.5 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                >
                                    {{ resolveStatus(player.status) }}
                                </span>
                                <span
                                    v-if="player.status === 'cancelled'"
                                    class="me-2 rounded-sm bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800 dark:bg-red-900 dark:text-red-300"
                                >
                                    {{ resolveStatus(player.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ player.created_at }}</td>
                            <td class="px-6 py-4">
                                <span v-if="player.winner" class="cursor-pointer font-medium text-yellow-600 hover:underline dark:text-yellow-500">
                                    <component :is="Trophy" />
                                </span>
                            </td>
                            <td class="flex gap-2 px-6 py-4">
                                <span
                                    v-if="player.status === 'pending'"
                                    @click="updateCard(player.id, 'paid')"
                                    title="Marcar como PAGADO"
                                    class="cursor-pointer font-medium text-green-600 hover:underline dark:text-green-500"
                                >
                                    <component :is="HandCoins" />
                                </span>
                                <span
                                    v-if="player.status === 'pending'"
                                    @click="updateCard(player.id, 'cancelled')"
                                    title="Marcar como NO PAGADO"
                                    class="cursor-pointer font-medium text-red-600 hover:underline dark:text-red-500"
                                >
                                    <component :is="BanIcon" />
                                </span>
                                <span
                                    @click="openModal(player)"
                                    title="Ver detalles"
                                    class="cursor-pointer font-medium text-yellow-600 hover:underline dark:text-yellow-500"
                                >
                                    <component :is="EyeIcon" />
                                </span>
                                <span
                                    v-if="player.status != 'cancelled'"
                                    title="Marcar como ganador"
                                    class="cursor-pointer font-medium text-yellow-600 hover:underline dark:text-yellow-500"
                                >
                                    <component :is="Trophy" />
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- MODAL -->
        <div
            id="popup-modal"
            tabindex="-1"
            class="fixed left-0 right-0 top-0 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0"
            style="z-index: 10000"
        >
            <div class="relative max-h-full w-full max-w-[70rem] p-4">
                <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                    <button
                        @click="closeModal"
                        type="button"
                        class="absolute end-2.5 top-3 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="popup-modal"
                    >
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                            />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-4 text-center md:p-5">
                        <h3 class="mb-5 text-2xl font-extrabold text-white">Detalles de la compra</h3>
                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                            Juagador: <br />
                            <b>{{ carton.name }}</b>
                        </h3>
                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                            Sorteo: <br /><b>{{ carton.game_name }}</b>
                        </h3>

                        <div class="mb-12 mt-12">
                            <h3 class="text-black-900 mb-4 text-center text-lg font-semibold">Cartón de bingo</h3>
                            <img class="mx-auto max-w-sm" :src="`/bingo-cards/${carton.card_path}`" alt="" />
                            <div class="bingo-card mx-auto mt-8 max-w-md overflow-hidden rounded-lg bg-white shadow-md">
                                <!-- Pie -->
                                <div class="bg-gray-100 px-4 py-2 text-center text-sm text-gray-600">
                                    Serial: {{ carton.serial_number }} | Estado:
                                    {{ resolveStatus(carton.status) }}
                                </div>
                            </div>
                        </div>

                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                            Referencia de pago: <br /><b>{{ carton.payment_reference }}</b>
                        </h3>
                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                            Fecha de compra: <br /><b>{{ formatDate(carton.created_at) }}</b>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
