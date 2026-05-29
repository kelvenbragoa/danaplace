<script setup>

import axios from 'axios';
import { ref, onMounted, onUnmounted, reactive, defineEmits, defineComponent,watch,computed } from "vue";
import moment from 'moment'
import {useToastr} from '../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let loadingDiv =ref(true);

const searchQuery = ref("");

// Lista de módulos (dados estáticos)
const modules = ref([
  { id: 1, name: "Manutenção", icon: "tool", description: "Gerencie atividades de manutenção preventiva e corretiva." },
  { id: 2, name: "Operação", icon: "activity", description: "Acompanhe e registre operações diárias." },
  { id: 3, name: "Stock", icon: "box", description: "Controle e monitoramento de estoque e inventário." },
  { id: 4, name: "Logística", icon: "truck", description: "Gestão de transportes e distribuição." },
  { id: 5, name: "Atas de Reunião", icon: "file-text", description: "Criação e organização de atas de reunião." },
  { id: 6, name: "Orçamento e Procurement", icon: "dollar-sign", description: "Planejamento orçamentário e aquisição de materiais." },
]);

// Filtragem dos módulos com base na pesquisa
const filteredModules = computed(() => {
  return modules.value.filter((mod) =>
    mod.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

onMounted(() => {
  document.querySelector("#sidebar")?.classList.add("collapsed");
  const sidebarToggle = document.querySelector(".sidebar-toggle");
  if (sidebarToggle) {
    sidebarToggle.style.pointerEvents = "none"; // Bloqueia cliques
    sidebarToggle.style.opacity = "0.5"; // Visualmente desativa
  }
});

onUnmounted(() => {
  document.querySelector("#sidebar")?.classList.remove("collapsed");
  const sidebarToggle = document.querySelector(".sidebar-toggle");
  if (sidebarToggle) {
    sidebarToggle.style.pointerEvents = "auto"; // Reativa cliques
    sidebarToggle.style.opacity = "1"; // Restaura aparência
  }
});

</script>

<template>
    <div v-if="loadingDiv">

    <h1 class="h3 mb-3 text-center">Módulos do Sistema</h1>
    <!-- Barra de Pesquisa -->
    <div class="d-flex justify-content-center mb-4">
      <input
        v-model="searchQuery"
        type="text"
        class="form-control w-50"
        placeholder="Pesquisar módulos..."
      />
    </div>

    <!-- Exibir Módulos -->
    <div class="row">
      <div
        v-for="mod in filteredModules"
        :key="mod.id"
        class="col-md-4 col-lg-3 mb-4"
      >
        
          <div class="card text-center shadow-sm">
          <router-link to="admin/dashboard">
            <div class="card-body">
              <vue-feather :type="mod.icon" size="40"></vue-feather>
              <h5 class="mt-2">{{ mod.name }}</h5>
              <p class="text-muted">{{ mod.description }}</p>
            </div>
          </router-link>
          </div>
        
        
      </div>
    </div>
    </div>
    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div> 
        </div>
    </div>
</template>