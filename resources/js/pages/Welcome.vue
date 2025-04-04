<script setup>
import axios from 'axios';
import { initFlowbite, Modal } from 'flowbite';
import { onMounted, ref } from 'vue';

const games = defineProps(['scheduled_games', 'in_progress_games']);

const carton = ref(null);

const step1 = ref(true);
const step2 = ref(false);
const step3 = ref(false);
const step4 = ref(false);
const step5 = ref(false);

const whatsapp = ref(false);

const chatWhatsapp = () => {
    whatsapp.value = !whatsapp.value;
};

const changeStep = (step) => {
    step1.value = step == 1 ? true : false;
    step2.value = step == 2 ? true : false;
    step3.value = step == 3 ? true : false;
    step4.value = step == 4 ? true : false;
    step5.value = step == 5 ? true : false;
};

const viewCarton = ref(false);
const markedNumbers = ref([]);
const serialNumber = ref('');
const referencia = ref('');
const cardNumbers = ref([]);
const numberCard = ref(null);
const loading = ref(false);
const forceHidden = ref(false);

const name = ref('');
const phone = ref('');
const email = ref('');

const generarCarton = (number = null) => {
    try {
        loading.value = true;
        // Limpiar estado previo
        viewCarton.value = false;
        errorMessage.value = ''; // Limpiar mensajes de error anteriores

        // Validación si se proporciona un número específico
        if (number !== null) {
            // Validar que no sea string vacío
            if (typeof number === 'string' && number.trim() === '') {
                throw new Error('Por favor ingrese un número');
            }

            const num = Number(number);

            // Validar que sea un número válido
            if (isNaN(num)) {
                throw new Error('Por favor ingrese un número válido');
            }

            // Validar que sea entero
            if (!Number.isInteger(num)) {
                throw new Error('El número debe ser entero');
            }

            // Validar rango
            if (num < 1 || num > 1000) {
                throw new Error('El número debe estar entre 1 y 1000');
            }
        }

        // Si pasa la validación, hacer la solicitud
        const url = number !== null ? `/card/generate/${selected_game.value}/${encodeURIComponent(number)}` : '/card/generate/' + selected_game.value;

        fetch(url, { method: 'get' })
            .then((response) => {
                // Primero verificar el estado de la respuesta
                if (!response.ok) {
                    // Si hay error, parsear el cuerpo de la respuesta como JSON
                    return response.json().then((errData) => {
                        // Rechazar con el error específico del servidor
                        throw new Error(errData.error || 'Error al generar el cartón');
                    });
                }
                return response.json();
            })
            .then(processCardData)
            .catch(handleError);
        loading.value = false;
    } catch (error) {
        loading.value = false;
        handleError(error);
    }
};

const buyGame = async (carton, name, email, phone, referencia) => {
    try {
        loading.value = true;

        // Estructura los datos del cartón correctamente
        const requestData = {
            name,
            email,
            phone,
            referencia,
            carton: {
                numbers: carton.numbers, // Asume que carton.numbers tiene la estructura {B: [...], I: [...], ...}
                serial: carton.serial,
                game_id: selected_game.value,
                amount: amountCard.value,
            },
        };

        const response = await axios.post('/card/buy', requestData);

        // Si necesitas redirección después de la compra
        if (response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
        } else {
            // Mostrar mensaje de éxito
            step5.value = true;
            step4.value = false;
            step1.value = false;
            step2.value = false;
            step3.value = false;

            carton.value = null;

            console.log('Respuesta completa:', response.data);
        }
    } catch (error) {
        // Manejo detallado de errores
        if (error.response) {
            // El servidor respondió con un status code fuera de 2xx
            console.error('Error de servidor:', error.response.data);
            alert(error.response.data.message || 'Error al procesar la compra');
        } else if (error.request) {
            // La petición fue hecha pero no hubo respuesta
            console.error('No hubo respuesta del servidor');
            alert('No se pudo conectar con el servidor');
        } else {
            // Error al configurar la petición
            console.error('Error:', error.message);
            alert('Error inesperado');
        }
    } finally {
        loading.value = false;
    }
};

const processCardData = (data) => {
    viewCarton.value = true;

    carton.value = data;
    serialNumber.value = data.serial;

    const originalNumbers = [data.numbers.B, data.numbers.I, data.numbers.N, data.numbers.G, data.numbers.O];

    cardNumbers.value = originalNumbers[0].map((_, colIndex) => originalNumbers.map((row) => row[colIndex]));

    markedNumbers.value = Object.values(data.numbers)
        .flat()
        .filter((num) => num !== null);
};

const selected_game = ref(null);
const amountCard = ref(null);

const selectGame = (game) => {
    selected_game.value = game.id;
    amountCard.value = game.price_per_card;
    viewCarton.value = false;
    carton.value = null;
    serialNumber.value = null;
    cardNumbers.value = [];
    markedNumbers.value = [];
};

const cleanData = () => {
    viewCarton.value = false;
    carton.value = null;
    serialNumber.value = null;
    cardNumbers.value = [];
    markedNumbers.value = [];
    selected_game.value = null;
    amountCard.value = null;

    step5.value = false;
    step4.value = false;
    step1.value = false;
    step2.value = false;
    step3.value = false;

    const modalElement = document.getElementById('popup-modal');
    if (modalElement) {
        // Crear una instancia del modal
        const modalInstance = new Modal(modalElement);
        // Cerrar el modal
        modalInstance.hide();
    }
};

function openModal() {
    step1.value = true;
    step5.value = false;
    step4.value = false;
    step2.value = false;
    step3.value = false;

    const modalElement = document.getElementById('popup-modal');
    if (modalElement) {
        // Crear una instancia del modal
        const modalInstance = new Modal(modalElement);
        // Abrir el modal
        modalInstance.show();
    }
}

const handleError = (error) => {
    console.error('Error:', error);
    // Usar un ref para mostrar el error en la UI en lugar de alert
    errorMessage.value = error.message;

    // O si prefieres usar alert:
    // alert(error.message);
};

// En tu componente, asegúrate de tener:
const errorMessage = ref('');

// Función para verificar si un número está marcado
const isFreeSpace = (rowIndex, colIndex) => {
    return rowIndex === 2 && colIndex === 2;
};

const isMarked = (num) => {
    return num !== null && markedNumbers.value.includes(num);
};

onMounted(() => {
    initFlowbite();
});
</script>

<template>
    <!-- ***** Preloader Start ***** -->
    <!-- <div id="js-preloader" class="js-preloader">
        <div class="preloader-inner">
            <span class="dot"></span>
            <div class="dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div> -->
    <!-- ***** Preloader End ***** -->

    <!-- ***** Header Area Start ***** -->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="/" class="logo">
                            <img src="images/logo.webp" alt="" />
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav">
                            <li><a href="#testimonios">Testimonios</a></li>
                            <li><a href="#" @click="openModal">Comprar</a></li>
                            <li><a href="#" title="Pronto">Descargar</a></li>
                            <li><a href="https://wa.link/u181s7" target="_blank">Ayuda</a></li>
                            <!--  <li v-if="$page.props.auth.user">
                                <Link :href="route('dashboard')">{{ $page.props.auth.user.name }} </Link>
                            </li>
                            <div v-else>
                                <li>
                                    <Link :href="route('login')"> Iniciar Sesión </Link>
                                </li>
                                <li>
                                    <Link :href="route('register')"> Registrarse </Link>
                                </li>
                            </div> -->
                        </ul>
                        <a class="menu-trigger">
                            <span>Menu</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- ***** Header Area End ***** -->

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="page-content">
                    <!-- ***** Banner Start ***** -->
                    <div class="main-banner">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="header-text">
                                    <h6>Bienvenidos a</h6>
                                    <h4><em>Bingo Full Gaming</em> el más popular de Venezuela.</h4>
                                    <div class="main-button">
                                        <a href="#" @click="openModal">Comprar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ***** Banner End ***** -->

                    <!-- ***** Details Start ***** -->
                    <div class="game-details">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>
                                    Con 1 Cartón de 100Bs Participas por un Premio Total de
                                    <b style="color: #f13d06">90000Bs</b>
                                </h2>
                            </div>
                            <div class="col-lg-12">
                                <a id="testimonios"></a>
                                <div class="content">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="center-info">
                                                <div class="text-center">
                                                    <h4>Mira un video para ver cómo funciona</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <img src="images/details-01.jpg" alt="" style="border-radius: 23px; margin-bottom: 30px" />
                                        </div>
                                        <div class="col-lg-4">
                                            <img src="images/details-02.jpg" alt="" style="border-radius: 23px; margin-bottom: 30px" />
                                        </div>
                                        <div class="col-lg-4">
                                            <img src="images/details-03.jpg" alt="" style="border-radius: 23px; margin-bottom: 30px" />
                                        </div>
                                        <div class="col-lg-12">
                                            <ol>
                                                <li>
                                                    <i style="color: #f13d06" class="fa-solid fa-circle-check"></i>
                                                    Con 100Bs participas en
                                                    <strong>todas las 14 rondas del día</strong>, eso quiere decir que tienes hasta 14 oportunidades
                                                    distintas de ganar.
                                                </li>
                                                <li class="mt-2">
                                                    <i style="color: #f13d06" class="fa-solid fa-circle-check"></i>
                                                    Es un bingo automático por lo que
                                                    <strong>no necesitas cantar BINGO</strong> para llevarte tu premio.
                                                </li>
                                                <li class="mt-2">
                                                    <i style="color: #f13d06" class="fa-solid fa-circle-check"></i>
                                                    Hacemos juegos todos los días a las 7pm de la noche.
                                                </li>
                                                <li class="mt-2">
                                                    <i style="color: #f13d06" class="fa-solid fa-circle-check"></i>
                                                    Tenemos meses jugando por lo que
                                                    <strong><a href="#">tenemos varios testimonios de ganadores</a></strong
                                                    >.
                                                </li>
                                                <li class="mt-2">
                                                    <i style="color: #f13d06" class="fa-solid fa-circle-check"></i>
                                                    Cada día hacemos distintas figuras por lo que con nuestro bingo
                                                    <strong>tienes más probabilidades de ganar</strong>.
                                                </li>
                                            </ol>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="main-border-button">
                                                <a href="#" @click="openModal">¡Quiero jugar un cartón!</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ***** Details End ***** -->
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <p>
                        Copyright © 2025 <b>Bingo Full Gaming</b>. <br />
                        Todos los derechos reservados.

                        <br />Powered by:
                        <a href="https://quantlas.tech" target="_blank" title="Quantlas"><b>Quantlas.tech</b></a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <div
        id="popup-modal"
        tabindex="-1"
        class="fixed left-0 right-0 top-0 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0"
        style="z-index: 10000"
        :class="forceHidden ? 'hidden' : ''"
    >
        <div class="relative max-h-full w-full max-w-[70rem] p-4">
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <button
                    @click="cleanData"
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
                    <div class="m-auto mb-4">
                        <ol class="m-auto w-full items-center space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse">
                            <li class="flex items-center space-x-2.5 text-blue-600 dark:text-blue-500 rtl:space-x-reverse">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 dark:border-blue-500"
                                    :class="step1 ? 'bg-blue-600 font-extrabold text-white dark:bg-blue-500' : ''"
                                >
                                    1
                                </span>
                                <span>
                                    <h3 class="font-medium text-blue-600 dark:text-blue-500">Sorteo</h3>
                                    <p class="text-sm">Escoja un sorteo</p>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2.5 text-blue-600 dark:text-blue-500 rtl:space-x-reverse">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 dark:border-blue-500"
                                    :class="step2 ? 'bg-blue-600 font-extrabold text-white dark:bg-blue-500' : ''"
                                >
                                    2
                                </span>
                                <span>
                                    <h3 class="font-medium text-blue-600 dark:text-blue-500">Cartón</h3>
                                    <p class="text-sm">Escoja su cartón</p>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2.5 text-gray-500 dark:text-gray-400 rtl:space-x-reverse">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-500 dark:border-gray-400"
                                    :class="step3 ? 'bg-blue-600 font-extrabold text-white dark:bg-blue-500' : ''"
                                >
                                    3
                                </span>
                                <span>
                                    <h3 class="font-medium text-blue-600 dark:text-blue-500">Jugador</h3>
                                    <p class="text-sm">Coloque sus datos</p>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2.5 text-gray-500 dark:text-gray-400 rtl:space-x-reverse">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-500 dark:border-gray-400"
                                    :class="step4 ? 'bg-blue-600 font-extrabold text-white dark:bg-blue-500' : ''"
                                >
                                    4
                                </span>
                                <span>
                                    <h3 class="font-medium text-blue-600 dark:text-blue-500">Información de pago</h3>
                                    <p class="text-sm">Haga su pago</p>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2.5 text-gray-500 dark:text-gray-400 rtl:space-x-reverse">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-500 dark:border-gray-400"
                                    :class="step5 ? 'bg-blue-600 font-extrabold text-white dark:bg-blue-500' : ''"
                                >
                                    5
                                </span>
                                <span>
                                    <h3 class="font-medium text-blue-600 dark:text-blue-500">Finalizar</h3>
                                    <p class="text-sm">Gracias por su compra</p>
                                </span>
                            </li>
                        </ol>
                    </div>

                    <hr class="my-4 text-gray-200 dark:text-gray-600" />

                    <div class="mb-12 mt-12" v-if="viewCarton">
                        <h3 class="mb-4 text-center text-lg font-semibold text-gray-900">Tu Cartón</h3>
                        <div class="bingo-card mx-auto max-w-md overflow-hidden rounded-lg bg-white shadow-md">
                            <!-- Encabezado -->
                            <div class="grid grid-cols-5 bg-blue-600 py-2 text-center font-bold text-white">
                                <span>B</span>
                                <span>I</span>
                                <span>N</span>
                                <span>G</span>
                                <span>O</span>
                            </div>

                            <!-- Números (filas) -->
                            <div
                                v-for="(row, rowIndex) in cardNumbers"
                                :key="rowIndex"
                                class="grid grid-cols-5 border-b border-gray-200 last:border-0"
                            >
                                <div
                                    v-for="(num, colIndex) in row"
                                    :key="colIndex"
                                    :class="[
                                        'border-r border-gray-200 p-3 text-center last:border-r-0',
                                        'flex h-12 items-center justify-center',
                                        isMarked(num) ? 'bg-green-50 text-green-800' : '',
                                        isFreeSpace(rowIndex, colIndex) ? 'bg-yellow-50 font-bold' : '',
                                    ]"
                                >
                                    <span v-if="!isFreeSpace(rowIndex, colIndex)">{{ num }}</span>
                                    <span v-else class="text-yellow-600">LIBRE</span>
                                </div>
                            </div>

                            <!-- Pie -->
                            <div class="bg-gray-100 px-4 py-2 text-center text-sm text-gray-600">Serial: {{ serialNumber }}</div>
                        </div>
                    </div>

                    <!-- STEP 1 -->
                    <div class="mb-12 mt-12" v-if="step1">
                        <h3 class="mb-4 text-center text-lg font-semibold text-gray-900 dark:text-black">Eliga un sorteo</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                            <div
                                v-for="game in games.in_progress_games"
                                class="bg-yellow-5 w-full max-w-sm cursor-pointer rounded-lg border border-green-900 p-4 shadow-sm sm:p-8"
                                :key="game.id"
                                @click="selectGame(game)"
                                :class="game.id === selected_game ? 'bg-green-600 text-white shadow-xl' : ''"
                            >
                                <h5 class="mb-4 text-xl font-medium text-gray-500 dark:text-gray-700">Jugando Ahora</h5>
                                <h4 class="mb-4 text-2xl font-bold text-gray-500 dark:text-gray-800">{{ game.date }}</h4>
                                <div class="flex items-baseline text-gray-900 dark:text-black">
                                    <span class="text-5xl font-extrabold tracking-tight">{{ game.name }}</span>
                                </div>
                                <div class="flex items-baseline text-gray-900 dark:text-gray-700">
                                    <span class="text-xl font-extrabold tracking-tight">{{ game.description }}</span>
                                </div>
                                <ul role="list" class="my-7 space-y-5">
                                    <!-- <li class="flex items-center">
                                        <span class="ms-3 text-base font-normal leading-tight text-gray-500 dark:text-gray-700">
                                            Hora: {{ game.time }}
                                        </span>
                                    </li> -->
                                    <li class="flex">
                                        <span
                                            class="me-2 rounded-sm bg-yellow-100 px-2.5 py-0.5 text-xl font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                        >
                                            Precio: ${{ game.price_per_card }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-for="game in games.scheduled_games"
                                class="bg-yellow-5 w-full max-w-sm cursor-pointer rounded-lg border border-green-900 p-4 shadow-sm sm:p-8"
                                @click="selectGame(game)"
                                :class="game.id === selected_game ? 'bg-green-600 text-white shadow-xl' : ''"
                            >
                                <h5 class="mb-4 text-xl font-medium text-gray-500 dark:text-gray-700">Próximos Sorteos</h5>
                                <h4 class="mb-4 text-2xl font-bold text-gray-500 dark:text-gray-800">{{ game.date }}</h4>
                                <div class="flex items-baseline text-gray-900 dark:text-black">
                                    <span class="text-5xl font-extrabold tracking-tight">{{ game.name }}</span>
                                </div>
                                <div class="flex items-baseline text-gray-900 dark:text-gray-700">
                                    <span class="text-xl font-extrabold tracking-tight">{{ game.description }}</span>
                                </div>
                                <ul role="list" class="my-7 space-y-5">
                                    <!-- <li class="flex items-center">
                                        <span class="ms-3 text-base font-normal leading-tight text-gray-500 dark:text-gray-700">
                                            Hora: {{ game.time }}
                                        </span>
                                    </li> -->
                                    <li class="flex">
                                        <span
                                            class="me-2 rounded-sm bg-yellow-100 px-2.5 py-0.5 text-xl font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                        >
                                            Precio: ${{ game.price_per_card }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="mb-12 mt-12" v-if="step2">
                        <h3 class="mb-4 text-center text-lg font-semibold text-gray-900 dark:text-black">Elige su cartón</h3>
                        <button
                            v-if="!loading"
                            @click="generarCarton()"
                            type="button"
                            class="mb-2 me-2 rounded-full bg-green-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                        >
                            {{ viewCarton ? 'Generar Nuevo Cartón' : 'Generar Cartón' }}
                        </button>
                        <button
                            v-else
                            disabled
                            type="button"
                            class="mb-2 me-2 rounded-full bg-green-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                        >
                            <svg
                                aria-hidden="true"
                                role="status"
                                class="me-3 inline h-4 w-4 animate-spin text-white"
                                viewBox="0 0 100 101"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                    fill="#E5E7EB"
                                />
                                <path
                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                    fill="currentColor"
                                />
                            </svg>
                            Generando...
                        </button>

                        <div class="inline-flex w-full items-center justify-center">
                            <hr class="my-8 h-px w-64 border-0 bg-gray-200 dark:bg-gray-700" />
                            <span class="absolute left-1/2 -translate-x-1/2 bg-white px-3 font-medium text-gray-900 dark:bg-gray-900 dark:text-black"
                                >o</span
                            >
                        </div>
                        <div class="m-auto mb-6 w-1/2">
                            <label for="large-input" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Large input</label>
                            <h3 class="mb-8 text-gray-400 dark:text-gray-400">Escoja un numero entre el 1 y el 1000</h3>
                            <input
                                v-model="numberCard"
                                type="number"
                                id="large-input"
                                min="1"
                                max="1000"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-4 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            />
                            <small v-if="errorMessage" class="mt-4 block text-xs text-red-500 dark:text-red-800">{{ errorMessage }}</small>
                            <button
                                @click="generarCarton(numberCard)"
                                :disabled="!numberCard"
                                type="button"
                                class="mt-4 rounded-lg bg-blue-700 px-3 py-2 text-center text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            >
                                Ver Cartón
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <div class="mx-auto mb-4 mt-12 max-w-md" v-if="step3">
                        <h3 class="mb-6 text-center text-lg font-semibold text-gray-900 dark:text-black">Coloque sus datos</h3>
                        <div class="space-y-4">
                            <div class="flex flex-col items-center">
                                <label for="name" class="mb-2 w-full max-w-xs text-sm font-medium text-gray-900 dark:text-black">Nombre</label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="name"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 bg-gray-50 p-3 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex flex-col items-center">
                                <label for="email" class="mb-2 w-full max-w-xs text-sm font-medium text-gray-900 dark:text-black">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="email"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 bg-gray-50 p-3 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex flex-col items-center">
                                <label for="phone" class="mb-2 w-full max-w-xs text-sm font-medium text-gray-900 dark:text-black">Teléfono</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    v-model="phone"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 bg-gray-50 p-3 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4 -->
                    <div class="mx-auto mb-4 mt-12 max-w-md" v-if="step4">
                        <h3 class="mb-4 text-center text-lg font-semibold text-gray-900 dark:text-black" v-if="step3">Haga su pago</h3>

                        <div class="w-full rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h5 class="mb-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-black">Pago Móvil</h5>

                            <p class="mb-1 font-normal text-gray-500 dark:text-gray-800">Mercantil (0105)</p>
                            <p class="mb-1 font-normal text-gray-500 dark:text-gray-800">Teléfono <b>04169964460</b></p>
                            <p class="mb-1 font-normal text-gray-500 dark:text-gray-800">Documento <b>22100667</b></p>

                            <div class="mt-4">
                                <p class="mb-1 font-normal text-gray-500 dark:text-gray-800">Número de referencia</p>
                                <input
                                    type="text"
                                    v-model="referencia"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 bg-gray-50 p-3 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5 -->
                    <div class="mx-auto mb-4 mt-12 max-w-md" v-if="step5">
                        <h3 class="mb-4 text-center text-2xl font-semibold text-gray-900 dark:text-black">Gracias por su compra</h3>
                        <h4 class="mb-4 text-center text-lg font-semibold text-gray-900 dark:text-black">
                            Su compra será procesada en los pr&oacute;ximos minutos
                        </h4>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">Importante:</span> Una vez que se haya realizado el pago, se le enviar&aacute; un correo con
                            el link para acceder a la plataforma.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="ms-3 inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-700 dark:focus:ring-gray-700"
                        :class="step2 ? 'cursor-pointer' : 'cursor-not-allowed'"
                        :hidden="step1 || step5"
                        @click="changeStep(step2 ? 1 : step3 ? 2 : 1 || step4 ? 3 : null || step5 ? 4 : null)"
                    >
                        Anterior
                    </button>
                    <button
                        type="button"
                        class="ms-3 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-700 dark:focus:ring-gray-700"
                        :class="
                            (step2 && carton === null) ||
                            (step2 && name === '') ||
                            (step2 && email === '') ||
                            (step2 && phone === '') ||
                            (step1 && selected_game === null)
                                ? 'cursor-pointer'
                                : 'cursor-not-allowed'
                        "
                        :hidden="step4 || step5"
                        :disabled="
                            (step1 && selected_game === null) ||
                            (step2 && carton === null) ||
                            (step3 && name === '') ||
                            (step3 && email === '') ||
                            (step3 && phone === '')
                        "
                        @click="changeStep(step1 ? 2 : step2 ? 3 : 1 || step3 ? 4 : null || step4 ? 5 : null)"
                    >
                        Siguiente
                    </button>
                    <button
                        type="button"
                        class="hover:text-white-700 dark:hover:text-white-700 ms-3 rounded-lg border border-gray-200 bg-blue-700 px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-blue-800 dark:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                        :class="step4 ? 'cursor-pointer' : 'cursor-not-allowed'"
                        :hidden="!step4"
                        :disabled="referencia == ''"
                        @click="buyGame(carton, name, email, phone, referencia)"
                    >
                        Comprar Cartón
                    </button>
                    <button
                        v-if="step5"
                        @click="cleanData()"
                        type="button"
                        class="hover:text-white-700 dark:hover:text-white-700 ms-3 rounded-lg border border-gray-200 bg-blue-700 px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-blue-800 dark:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                        :class="step5 ? 'cursor-pointer' : 'cursor-not-allowed'"
                        data-modal-hide="popup-modal"
                    >
                        Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div v-if="whatsapp" id="whatsapp-chat" class="hide-wsp">
        <div class="header-chat">
            <div class="head-home">
                <div class="info-avatar">
                    <img src="images/logo.webp" />
                </div>
            </div>

            <div class="get-new">
                <div id="get-nama">Bingo Full Gaming</div>
                <div id="get-label">El Mejor Bingo Online de Venezuela</div>
            </div>
        </div>

        <div class="start-chat">
            <div class="first-msg">
                <span>
                    ¡Hola! somos <b>Bingo Full Gaming</b>.
                    <br />
                    <br />
                    Envía un mensaje y serás atendido brevemente.
                </span>
            </div>

            <div class="blanter-msg">
                <a href="https://wa.link/u181s7" target="_blank" id="send-it"
                    >Abrir Chat <span class="iconify" data-icon="mdi:paper-airplane" style="color: white"></span
                ></a>
            </div>
        </div>
        <div id="get-number"></div>
        <a class="close-chat" @click="chatWhatsapp" href="#">×</a>
    </div>
    <a href="#" class="button-float" @click="chatWhatsapp" title="¿Necesitas ayuda?" style="margin-right: 5%">
        <i class="fa-brands fa-whatsapp" style="font-size: 2.7rem; display: flex; align-items: center; height: 100%; justify-content: center"></i>
    </a>
    <div class="button-hide">
        <div class="button-float-push">
            <strong>1</strong>
        </div>
    </div>

    <div class="button-hide2">
        <div class="button-float-msg">¿Necesitas Ayuda?</div>
    </div>
</template>
<style scoped>
.bingo-card {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-radius: 0.5rem;
    overflow: hidden;
}

.card-header span {
    padding: 0.5rem;
}

.card-grid {
    border-left: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
}

.card-footer {
    border-top: 1px solid #e5e7eb;
}
</style>
