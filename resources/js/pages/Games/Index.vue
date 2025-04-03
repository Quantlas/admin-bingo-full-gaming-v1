<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { EditIcon, EyeIcon, PlayCircleIcon, StopCircleIcon, TrashIcon } from 'lucide-vue-next';
import { computed, defineEmits, ref, type PropType } from 'vue';

const emit = defineEmits(['update:games']);

const props = defineProps({
    games: {
        type: Array as PropType<Game[]>,
        required: true,
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Games',
        href: '/dashboard/games',
    },
];

interface Game {
    id: number | null;
    name: string;
    description: string;
    price_per_card: number;
    start_time: string;
    status: string;
}

const showNew = ref(false);

const sorteo = ref({
    id: null as number | null,
    name: null as string | null,
    description: null as string | null,
    price_per_card: null as number | null,
    start_time: '',
    status: null as string | null,
});

const editGame = (game: Game) => {
    sorteo.value = game;
    showNew.value = true;
};

const toggleNew = () => {
    showNew.value = !showNew.value;
};

const formattedStartTime = computed({
    get: () => sorteo.value.start_time.split(' ')[0], // Extrae solo la parte de la fecha
    set: (value: string) => {
        sorteo.value.start_time = `${value} 00:00:00`; // Actualiza la fecha original
    },
});

const loading = ref(false);

const errorMessage = ref('');

const saveData = () => {
    if (!sorteo.value.name || !sorteo.value.price_per_card || !sorteo.value.start_time) {
        errorMessage.value =
            'El campo ' +
            (!sorteo.value.name ? 'Nombre del Sorteo' : !sorteo.value.price_per_card ? 'Precio del cartón' : 'Fecha del Sorteo') +
            ' es obligatorio.';
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    const dataToSend = {
        ...sorteo.value,
        start_time: `${formattedStartTime.value} 00:00:00`, // Agrega la hora si es necesario
        status: 'scheduled',
    };

    if (sorteo.value.id) {
        dataToSend.id = sorteo.value.id;
    }

    if (sorteo.value.id) {
        dataToSend.id = sorteo.value.id;
        // Envía los datos al backend
        axios
            .put(`/games/${sorteo.value.id}`, dataToSend)
            .then((response) => {
                loading.value = false;
                showNew.value = false;
                sorteo.value = {
                    id: null,
                    name: '',
                    description: '',
                    price_per_card: 1,
                    start_time: '',
                    status: '',
                };
                // Limpia la lista actual y agrega los juegos actualizados
                props.games.length = 0; // Elimina los datos antiguos
                response.data.games.forEach((game: Game) => props.games.push(game)); // Agrega los juegos nuevos
                console.log(response.data);
            })
            .catch((error) => {
                loading.value = false;
                console.error(error);
            });
    } else {
        // Envía los datos al backend
        axios
            .post('/games/create', dataToSend)
            .then((response) => {
                loading.value = false;
                showNew.value = false;
                sorteo.value = {
                    id: null,
                    name: '',
                    description: '',
                    price_per_card: 1,
                    start_time: '',
                    status: '',
                };
                // Limpia la lista actual y agrega los juegos actualizados
                props.games.length = 0; // Elimina los datos antiguos
                response.data.games.forEach((game: Game) => props.games.push(game)); // Agrega los juegos nuevos
                console.log(response.data);
            })
            .catch((error) => {
                loading.value = false;
                console.error(error);
            });
    }
};

const deleteGame = (game: Game) => {
    axios
        .delete(`/games/${game.id}`)
        .then((response) => {
            // Limpia la lista actual y agrega los juegos actualizados
            props.games.length = 0; // Elimina los datos antiguos
            response.data.games.forEach((game: Game) => props.games.push(game)); // Agrega los juegos nuevos
            console.log(response.data);
        })
        .catch((error) => {
            errorMessage.value = error.response.data.message;
            console.error(error);
        });
};

const changeStatus = (game: Game, status: string) => {
    axios
        .post(`/games/${game.id}/status`, {
            status: status,
        })
        .then((response) => {
            // Limpia la lista actual y agrega los juegos actualizados
            props.games.length = 0; // Elimina los datos antiguos
            response.data.games.forEach((game: Game) => props.games.push(game)); // Agrega los juegos nuevos
            console.log(response.data);
        })
        .catch((error) => {
            console.error(error);
        });
};

const resolveStatus = (status: string) => {
    switch (status) {
        case 'scheduled':
            return 'Programado';
        case 'in_progress':
            return 'Activo';
        case 'completed':
            return 'Finalizado';
        case 'canceled':
            return 'Cancelado';
        default:
            return 'Desconocido';
    }
};

const closeAlert = () => {
    errorMessage.value = '';
};
</script>

<template>
    <Head title="Games" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-12 flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                v-if="errorMessage"
                @click="closeAlert"
                id="alert-2"
                class="mb-4 flex items-center rounded-lg bg-red-50 p-4 text-red-800 dark:bg-gray-800 dark:text-red-400"
                role="alert"
            >
                <svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                    />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">
                    {{ errorMessage }}
                </div>
                <button
                    type="button"
                    class="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-200 focus:ring-2 focus:ring-red-400 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"
                    data-dismiss-target="#alert-2"
                    aria-label="Close"
                >
                    <span class="sr-only">Close</span>
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                        />
                    </svg>
                </button>
            </div>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400 rtl:text-right">
                    <caption class="bg-white p-5 text-left text-lg font-semibold text-gray-900 dark:bg-gray-800 dark:text-white rtl:text-right">
                        Juegos de Bingo
                        <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">Aquí puedes ver todos los juegos de Bingo.</p>
                        <div class="mt-4">
                            <button
                                @click="toggleNew"
                                class="rounded-md bg-blue-700 px-3 py-2 text-center text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            >
                                Nuevo juego
                            </button>
                        </div>
                    </caption>
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nombre</th>
                            <th scope="col" class="px-6 py-3">Descripción</th>
                            <th scope="col" class="px-6 py-3">Precio</th>
                            <th scope="col" class="px-6 py-3">Fecha sorteo</th>
                            <th scope="col" class="px-6 py-3">Estatus</th>
                            <th scope="col" class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="game in props.games"
                            class="border-b border-gray-200 odd:bg-white even:bg-gray-50 dark:border-gray-700 odd:dark:bg-gray-900 even:dark:bg-gray-800"
                        >
                            <th scope="row" class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ game.name }}
                            </th>
                            <td class="px-6 py-4">{{ game.description }}</td>
                            <td class="px-6 py-4">{{ game.price_per_card }}</td>
                            <td class="px-6 py-4">{{ game.start_time }}</td>
                            <td class="px-6 py-4">{{ resolveStatus(game.status) }}</td>
                            <td class="flex gap-2 px-6 py-4">
                                <span
                                    v-if="game.status === 'scheduled'"
                                    title="Editar Sorteo"
                                    class="cursor-pointer font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    @click="editGame(game)"
                                >
                                    <component :is="EditIcon" />
                                </span>
                                <span
                                    v-if="game.status === 'scheduled'"
                                    title="Iniciar Sorteo"
                                    @click="changeStatus(game, 'in_progress')"
                                    class="cursor-pointer font-medium text-green-600 hover:underline dark:text-green-500"
                                >
                                    <component :is="PlayCircleIcon" />
                                </span>
                                <span
                                    v-if="game.status === 'in_progress'"
                                    title="Finalizar Sorteo"
                                    @click="changeStatus(game, 'completed')"
                                    class="cursor-pointer font-medium text-red-600 hover:underline dark:text-red-500"
                                >
                                    <component :is="StopCircleIcon" />
                                </span>
                                <span
                                    v-if="game.status != 'scheduled'"
                                    title="Disponible pronto"
                                    class="cursor-pointer font-medium text-yellow-600 hover:underline dark:text-yellow-500"
                                >
                                    <component :is="EyeIcon" />
                                </span>
                                <span
                                    v-if="game.status === 'scheduled'"
                                    title="Eliminar Sorteo"
                                    @click="deleteGame(game)"
                                    class="cursor-pointer font-medium text-red-600 hover:underline dark:text-red-500"
                                >
                                    <component :is="TrashIcon" />
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showNew" class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold text-gray-900" id="modal-title">Crear nuevo sorteo</h3>
                                    <div class="mt-2">
                                        <div class="my-4">
                                            <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-black"
                                                >Nombre del Sorteo *</label
                                            >
                                            <input
                                                v-model="sorteo.name"
                                                type="text"
                                                id="name"
                                                class="block w-[400px] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                                placeholder="Nombre del sorteo"
                                                required
                                            />
                                        </div>
                                        <div class="my-4">
                                            <label for="description" class="mb-2 block text-sm font-medium text-gray-900 dark:text-black"
                                                >Descripción</label
                                            >
                                            <input
                                                v-model="sorteo.description"
                                                type="text"
                                                id="description"
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                                placeholder="Descripción"
                                            />
                                        </div>
                                        <div class="my-4">
                                            <label for="start_time" class="mb-2 block text-sm font-medium text-gray-900 dark:text-black"
                                                >Fecha del Sorteo *</label
                                            >
                                            <input
                                                v-model="formattedStartTime"
                                                type="date"
                                                id="start_time"
                                                class="block w-[400px] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                                required
                                            />
                                        </div>
                                        <div class="my-4">
                                            <label for="price_per_card" class="mb-2 block text-sm font-medium text-gray-900 dark:text-black"
                                                >Precio del cartón *</label
                                            >
                                            <input
                                                v-model="sorteo.price_per_card"
                                                type="number"
                                                id="price_per_card"
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                                placeholder="Precio"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="text-red-600" v-if="errorMessage">{{ errorMessage }}</span>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                @click="saveData()"
                                type="button"
                                class="shadow-xs inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500 sm:ml-3 sm:w-auto"
                            >
                                {{ loading ? 'Cargando...' : 'Guardar' }}
                            </button>
                            <button
                                @click="showNew = false"
                                type="button"
                                class="shadow-xs mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
