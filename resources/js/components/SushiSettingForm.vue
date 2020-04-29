<template>
  <div class="details">
	<h2 class="section-title">Sushi Settings</h2>
    <div v-if="!showForm">
      <!-- form display control and confirmations  -->
      <!-- Values-only when form not active -->
      <v-row>
        <v-col cols="4"><strong>Customer ID: </strong>{{ form.customer_id }}</v-col>
      	<v-col cols="4"><strong>Requestor ID: </strong>{{ form.requestor_id }}</v-col>
      	<v-col cols="4"><strong>API Key: </strong>{{ form.API_key }}</v-col>
        <v-col cols="12">
          <strong>Support Email: </strong><a :href="'mailto:'+form.support_email">{{ form.support_email }}</a>
        </v-col>
      </v-row>
      <v-row>
    	<v-col cols="4">
           <v-btn small color="primary" type="button" @click="swapForm" class="section-action">edit</v-btn>
        </v-col>
        <v-col cols="4">
          <v-btn small color="secondary" type="button" @click="testSettings">test</v-btn>
        </v-col>
        <v-col cols="4">
          <v-btn small class='btn btn-danger' type="button" @click="destroy(setting.id)">Delete</v-btn></td>
        </v-col>
      </v-row>
      <v-row>
        <span class="form-good" role="alert" v-text="success"></span>
        <span class="form-fail" role="alert" v-text="failure"></span>
        <div v-if="showTest">
          <div>{{ testStatus }}</div>
          <div v-for="row in testData">{{ row }}</div>
        </div>
      </v-row>
    </div>

    <!-- display form if manager has activated it. onSubmit function closes and resets showForm -->
    <div v-else>
      <v-row>
        <form method="POST" action="" @submit.prevent="formSubmit" @keydown="form.errors.clear($event.target.name)"
              class="in-page-form">
          <v-col>
            <v-text-field v-model="form.customer_id" label="Customer ID" outlined></v-text-field>
          </v-col>
          <v-col>
            <v-text-field v-model="form.requestor_id" label="Requestor ID" outlined></v-text-field>
          </v-col>
          <v-col>
            <v-text-field v-model="form.API_key" label="API_key" outlined></v-text-field>
          </v-col>
          <v-col>
            <v-text-field v-model="form.support_email" label="Support Email" outlined></v-text-field>
          </v-col>
          <v-btn small color="primary" type="submit" :disabled="form.errors.any()">
            Save Settings
          </v-btn>
          <v-btn small type="button" @click="hideForm">cancel</v-btn>
        </form>
      </v-row>
    </div>
  </div>
</template>

<script>
    import Form from '@/js/plugins/Form';
    import Swal from 'sweetalert2';
    window.Form = Form;
    export default {
        props: {
                setting: { type:Object, default: () => {} },
               },
        data() {
            return {
                success: '',
                failure: '',
                status: '',
				showForm: false,
                showTest: false,
                testData: '',
                testStatus: '',
                form: new window.Form({
                    customer_id: this.setting.customer_id,
                    requestor_id: this.setting.requestor_id,
                    API_key: this.setting.API_key,
                    support_email: this.setting.support_email,
                    inst_id: this.setting.inst_id,
                    prov_id: this.setting.prov_id,
                })
            }
        },
        methods: {
            formSubmit (event) {
	            this.form.post('/sushisettings-update')
	                // .then( function(response) {
                    .then( (response) => {
	                    this.warning = '';
	                    this.confirm = 'Settings successfully updated.';
	                });
                this.showForm = false;
	        },
            swapForm (event) {
                this.showForm = true;
			},
            hideForm (event) {
                this.showForm = false;
			},
            destroy (settingid) {
                var self = this;
                Swal.fire({
                  title: 'Are you sure?',
                  text: "Deleting these settings cannot be reversed, only manually recreated.",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, proceed'
                }).then((result) => {
                  if (result.value) {
                      axios.delete('/sushisettings/'+settingid)
                           .then( (response) => {
                               if (response.data.result) {
                                   self.failure = '';
                                   self.success = response.data.msg;
                                   self.form.customer_id = '';
                                   self.form.requestor_id = '';
                                   self.form.API_key = '';
                                   self.form.support_email = '';
                               } else {
                                   self.success = '';
                                   self.failure = response.data.msg;
                               }
                           })
                           .catch({});
                  }
                })
                .catch({});
            },
            testSettings (event) {
                var self = this;
                self.showTest = true;
                self.testData = '';
                self.testStatus = "... Working ...";
                axios.get('/sushisettings-test'+'?prov_id='+self.setting.prov_id+'&'
                                               +'requestor_id='+this.form.requestor_id+'&'
                                               +'customer_id='+this.form.customer_id+'&'
                                               +'apikey='+this.form.API_key)
                     .then( function(response) {
                        if ( response.data.result == '') {
                            self.testStatus = "No results!";
                        } else {
                            self.testStatus = response.data.result;
                            self.testData = response.data.rows;
                        }
                    })
                   .catch(error => {});
            },
        },
        mounted() {
            this.showForm = false;
            console.log('SushiSettingForm Component mounted.');
        }
    }
</script>

<style>
.form-good {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    color: green;
}
.form-fail {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    color: red;
}
</style>