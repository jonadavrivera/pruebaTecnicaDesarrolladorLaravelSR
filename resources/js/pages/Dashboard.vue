<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { onMounted, reactive, ref } from 'vue';

type ReservationItem = {
    id: number;
    meeting_room_id: number;
    user_id: number;
    start_time: string;
    end_time: string;
    title: string | null;
    description: string | null;
    status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
};

type ReservationPayload = {
    data?: ReservationItem[];
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const reservations = ref<ReservationItem[]>([]);
const isLoading = ref(false);
const isSaving = ref(false);
const editingId = ref<number | null>(null);
const message = ref('');
const errorMessage = ref('');

const form = reactive({
    meeting_room_id: 1,
    start_time: '',
    end_time: '',
    title: '',
    description: '',
    status: 'confirmed' as ReservationItem['status'],
});

const statusOptions: ReservationItem['status'][] = ['pending', 'confirmed', 'cancelled', 'completed'];

function toDateTimeLocal(input: string): string {
    if (!input) {
        return '';
    }

    const date = new Date(input);
    const offsetMs = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offsetMs).toISOString().slice(0, 16);
}

function resetForm(): void {
    editingId.value = null;
    form.meeting_room_id = 1;
    form.start_time = '';
    form.end_time = '';
    form.title = '';
    form.description = '';
    form.status = 'confirmed';
}

async function loadReservations(): Promise<void> {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/api/reservations', {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('No fue posible cargar las reservas');
        }

        const payload = (await response.json()) as ReservationPayload;
        reservations.value = payload.data ?? [];
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Error inesperado al cargar';
    } finally {
        isLoading.value = false;
    }
}

async function submitForm(): Promise<void> {
    isSaving.value = true;
    message.value = '';
    errorMessage.value = '';

    const endpoint = editingId.value ? `/api/reservations/${editingId.value}` : '/api/reservations';
    const method = editingId.value ? 'PUT' : 'POST';

    try {
        const response = await fetch(endpoint, {
            method,
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                meeting_room_id: Number(form.meeting_room_id),
                start_time: form.start_time,
                end_time: form.end_time,
                title: form.title || null,
                description: form.description || null,
                status: form.status,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload?.message ?? 'No fue posible guardar la reserva');
        }

        message.value = editingId.value
            ? 'Reserva actualizada correctamente'
            : 'Reserva creada correctamente';

        resetForm();
        await loadReservations();
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Error inesperado al guardar';
    } finally {
        isSaving.value = false;
    }
}

function setEdit(item: ReservationItem): void {
    editingId.value = item.id;
    form.meeting_room_id = item.meeting_room_id;
    form.start_time = toDateTimeLocal(item.start_time);
    form.end_time = toDateTimeLocal(item.end_time);
    form.title = item.title ?? '';
    form.description = item.description ?? '';
    form.status = item.status;
}

async function removeReservation(id: number): Promise<void> {
    const confirmDelete = window.confirm('¿Seguro que deseas eliminar esta reserva?');
    if (!confirmDelete) {
        return;
    }

    message.value = '';
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/reservations/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload?.message ?? 'No fue posible eliminar la reserva');
        }

        message.value = 'Reserva eliminada correctamente';

        if (editingId.value === id) {
            resetForm();
        }

        await loadReservations();
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Error inesperado al eliminar';
    }
}

onMounted(async () => {
    await loadReservations();
});
</script>

<template>
    <Head title="Demo CRUD Reservas" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold">Demo CRUD de Reservas (API)</h1>

        <section class="rounded-lg border border-gray-200  p-4">
            <h2 class="mb-4 text-lg font-medium">
                {{ editingId ? `Editar reserva #${editingId}` : 'Crear reserva' }}
            </h2>

            <form class="grid gap-3 md:grid-cols-2" @submit.prevent="submitForm">
                <label class="flex flex-col gap-1 text-sm">
                    Sala (meeting_room_id)
                    <input
                        v-model.number="form.meeting_room_id"
                        class="rounded border border-gray-300 px-3 py-2"
                        type="number"
                        min="1"
                        required
                    />
                </label>

                <label class="flex flex-col gap-1 text-sm">
                    Estado
                    <select
                        v-model="form.status"
                        class="rounded border border-gray-300 px-3 py-2"
                    >
                        <option
                            v-for="status in statusOptions"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </option>
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-sm">
                    Inicio
                    <input
                        v-model="form.start_time"
                        class="rounded border border-gray-300 px-3 py-2"
                        type="datetime-local"
                        required
                    />
                </label>

                <label class="flex flex-col gap-1 text-sm">
                    Fin
                    <input
                        v-model="form.end_time"
                        class="rounded border border-gray-300 px-3 py-2"
                        type="datetime-local"
                        required
                    />
                </label>

                <label class="md:col-span-2 flex flex-col gap-1 text-sm">
                    Título
                    <input
                        v-model="form.title"
                        class="rounded border border-gray-300 px-3 py-2"
                        type="text"
                    />
                </label>

                <label class="md:col-span-2 flex flex-col gap-1 text-sm">
                    Descripción
                    <textarea
                        v-model="form.description"
                        class="min-h-20 rounded border border-gray-300 px-3 py-2"
                    />
                </label>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button
                        class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-60"
                        type="submit"
                        :disabled="isSaving"
                    >
                        {{ isSaving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Crear') }}
                    </button>

                    <button
                        v-if="editingId"
                        class="rounded border border-gray-300 px-4 py-2"
                        type="button"
                        @click="resetForm"
                    >
                        Cancelar edición
                    </button>
                </div>
            </form>

            <p v-if="message" class="mt-3 text-sm text-green-600">{{ message }}</p>
            <p v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</p>
        </section>

        <section class="rounded-lg border border-gray-200  p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-medium">Listado de reservas</h2>
                <button
                    class="rounded border border-gray-300 px-3 py-1 text-sm"
                    type="button"
                    @click="loadReservations"
                >
                    Recargar
                </button>
            </div>

            <p v-if="isLoading" class="text-sm text-gray-500">Cargando...</p>
            <p v-else-if="reservations.length === 0" class="text-sm text-gray-500">
                No hay reservas para mostrar.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="px-2 py-2">ID</th>
                            <th class="px-2 py-2">Sala</th>
                            <th class="px-2 py-2">Usuario</th>
                            <th class="px-2 py-2">Inicio</th>
                            <th class="px-2 py-2">Fin</th>
                            <th class="px-2 py-2">Estado</th>
                            <th class="px-2 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in reservations"
                            :key="item.id"
                            class="border-b border-gray-100"
                        >
                            <td class="px-2 py-2">{{ item.id }}</td>
                            <td class="px-2 py-2">{{ item.meeting_room_id }}</td>
                            <td class="px-2 py-2">{{ item.user_id }}</td>
                            <td class="px-2 py-2">{{ item.start_time }}</td>
                            <td class="px-2 py-2">{{ item.end_time }}</td>
                            <td class="px-2 py-2">{{ item.status }}</td>
                            <td class="px-2 py-2">
                                <div class="flex gap-2">
                                    <button
                                        class="rounded border border-blue-300 px-2 py-1 text-blue-700"
                                        type="button"
                                        @click="setEdit(item)"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        class="rounded border border-red-300 px-2 py-1 text-red-700"
                                        type="button"
                                        @click="removeReservation(item.id)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
