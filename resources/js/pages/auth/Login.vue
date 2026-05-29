<script setup>

import axios from 'axios';
import {reactive, ref} from 'vue';


    const form = reactive ({
        email: '',
        password: ''
    });

    const loading = ref(false);

    const errorMessage = ref('');
    


    const handleSubmit = () => {
      loading.value = true;
      errorMessage.value = '';
        axios.post('/login',{
          email: form.email.toLowerCase(),
          password:form.password
        })
        .then((response)=>{
            console.log(response.data.role_id)
            
            if(response.data.role_id == 1){
              window.location.href="/admin/home";
            }

            if(response.data.role_id == 2){
              window.location.href="/manager/dashboard";
            }
          

            if(response.data.role_id == 4){
              window.location.href="/operator/maintenance/dashboard";
            }

            if(response.data.role_id == 3){
              window.location.href="/manager/maintenance/dashboard";
            }

            if(response.data.role_id == 8){
              window.location.href="/admin/destination/dashboard";
            }

            if(response.data.role_id == 11){
              window.location.href="/admin/stock/dashboard";
            }

            // window.location.href="/admin/dashboard";
        })
        .catch((error)=>{

          errorMessage.value = error.response.data.message
          
        })
        .finally(()=>{
          loading.value = false;
        })
    }


</script>


<template>

<div class="form-body">
        <div class="website-logo">
            <a href="index.html">
                <div class="logo">
                    
                </div>
            </a>
        </div>
        <div class="row">
            <div class="img-holder">
                <div class="bg"></div>
                <div class="info-holder">

                </div>
            </div>
            <div class="form-holder">
              
              
                <div class="form-content">
                    <div class="form-items align-center">
                      <img src="files/img/sys/companylogo.png" alt="" style="width: 100%;">
                      <!-- <img src="files/img/sys/volvopenta.png" alt="" style="width: 100%;"> -->
                      
                        
                        <!-- <div v-if="errorMessage" class="alert alert-danger" role="alert">
                                        <div class="m-2">
                                            {{errorMessage}}
                                        </div>
                                    </div>
                        <br> -->
                    <div v-if="errorMessage"  class="alert alert-danger alert-dismissible fade show with-icon" role="alert">
                            {{errorMessage}}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <form @submit.prevent="handleSubmit">
                          
                            <input class="form-control"  v-model="form.email" type="email" name="email" placeholder="E-mail Address" required>
                            <input class="form-control" v-model="form.password" type="password" name="password" placeholder="Password" required>
                            <div class="form-button">
                                <button id="submit" type="submit" class="ibtn" :disabled="loading">
                                  <!-- <div v-if="loading" class="spinner-border spinner-border-sm" role="status"></div> -->
                                  <span v-if="loading">Por Favor Aguarde ...</span>
                                  <span v-else>Entrar</span>
                                  </button> 
                            </div>
                        </form>
                        <div class="form-footer" style="margin-top: 500px;">
                        <div class="text-muted text-center">
                          <small>Desenvolvido pela INOVATIS MZ LTD</small>
                        </div>
                  </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						

						<div class="card">
							<div class="text-center mt-4">
								<h1 class="h2">Bem Vindo de Volta</h1>
								<h1 class="h2">InoGest M+D</h1>
                <h1 class="h2">Industrial Engines</h1>
								<p class="lead">
									Entre com a sua conta
								</p>
							</div>
							<div class="card-body">

								<div class="m-sm-4">

									<div class="text-center">
										<img src="/files/img/sys/logoinogestt.png" alt="InoGest" class="img-fluid" width="350" height="250" />
									</div>

                                    
                                   

									<form @submit.prevent="handleSubmit">

										<div class="mb-3">
											<label class="form-label">Endereço de email</label>
											<input class="form-control form-control-lg" type="email" v-model="form.email" placeholder="Introduza o email" required />
										</div>

										<div class="mb-3">
											<label class="form-label">Password</label>
											<input class="form-control form-control-lg" type="password" v-model="form.password" placeholder="Introduza o password" required/> 
										</div>
										
										<div class="text-center mt-3">
											
                                            <button type="submit" class="btn btn-lg btn-primary" :disabled="loading">
                                            <div v-if="loading" class="spinner-border spinner-border-sm" role="status"></div>
                                            <span v-else>Entrar</span>
                                            </button>
										</div>

									</form>
									
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main> -->
</template>