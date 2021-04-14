<template>
    <div>
        <div class="float-left">
        <p>
                <a class="collapse-menu active p-1 pl-2 pr-2 m-1 paymentPlanH" type="button" data-toggle="collapse" @click="closeOther('paymentPlan','paymentPlanH')" href=".paymentPlan" aria-expanded="false" aria-controls="collapseExample">
                    Payment Plan
                </a>
                <a class="collapse-menu p-1 pl-2 pr-2 m-1 addPaymentH" type="button" data-toggle="collapse"  @click="closeOther('addPayment','addPaymentH')" href=".addPayment" aria-expanded="false" aria-controls="collapseExample1">
                    <i class="fas fa-plus"></i> Add Payment
                </a>
               
        </p>
        </div>
        <div class="float-right">
            <h5>
                Remaining Balance: $ <span>{{ balance }}</span>
            </h5>
        </div>
        <div class="clearfix"></div>
        <div class="collapse show collapse-menu-body paymentPlan">
        <div class="card card-body">
            <table
                class="custom-table"
                id="dataTable"
                width="100%"
                cellspacing="0"
            >
            <thead>
                <tr>
                    <th class='text-center'></th>
                    <th class='text-center'>Month</th>
                    <th class='text-center'>Amount Due</th>
                    <th class='text-center'>Recomended Pay Date</th>
                    <!-- <th class='text-center'>Amount Paid</th> -->
                    <!-- <th class='text-center'>Post Date</th> -->
                    <th class='text-center'></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(sched,index) in detail.payment_sched" :key="index"> 
                    <td class='text-center'></td>
                    <td class='text-center'>{{ index + 1 }}</td>
                    <td class='text-center'>{{ sched.payable_amount }}</td>
                    <td class='text-center'>{{ sched.due_date | dateFormat }}</td>
                    <td class='text-center'><div class="form-group mt-3">
                            <select class="form-control" id="exampleFormControlSelect1">
                            <option>Actions</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>
        </div>
        <div class="collapse collapse-menu-body addPayment">
        <div class="card card-body">
            <div class="row align-items-center">
                <div class="col-md">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Amount:</label>
                        <input type="number"  min="0" class="form-control input-custom" id="amount" aria-describedby="emailHelp">
                    </div>
                </div>
                <div class="col-md">
                    <date-picker
                            v-model="edit_due_date['payment_'+detail.id]"
                            :masks="{L:'DD/MM/YYYY'}"
                            :max-date="new Date()"
                    >
                     <template v-slot="{ inputValue, inputEvents }">
                         <div class="form-group">
                            <label for="exampleInputEmail1">Post Date:</label>
                            <input type="text" :value="inputValue" v-on="inputEvents" class="form-control input-custom" id="postdate" aria-describedby="emailHelp">
                        </div>
                     </template>
                    </date-picker>
                    
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Notes:</label>
                        <input type="text" class="form-control input-custom" id="notes" aria-describedby="emailHelp">
                    </div>
                </div>
                <div class="col-md text-center ">
                    <div class="form-group mt-4">
                        <button @click="savePayment(detail.id)" class="btn btn-success btn-square align-middle" style="width:100%"> <i class="fas fa-plus"></i> Add Payment </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix m-3" ></div>
        <div class="card card-body">
            <table
                class="custom-table"
                id="dataTable"
                width="100%"
                cellspacing="0"
            >
            <thead>
                <tr>
                    <th class='text-center'>Amount</th>
                    <th class='text-center'>Post Date</th>
                    <th class='text-center'>Notes</th>
                    <!-- <th class='text-center'>Amount Paid</th> -->
                    <!-- <th class='text-center'>Post Date</th> -->
                    <th class='text-center'></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(sched,index) in detail.payment_sched" :key="index"> 
                    <td class='text-center'>{{ index + 1 }}</td>
                    <td class='text-center'>{{ sched.payable_amount }}</td>
                    <td class='text-center'>{{ sched.due_date | dateFormat }}</td>
                    <td class='text-center'>
                        <div class="form-group mt-3">
                            <select class="form-control" id="exampleFormControlSelect1">
                            <option>Actions</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>
        </div>
        <div class="collapse collapse-menu-body invoices">
        <div class="card card-body">
            1Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
        </div>
        </div>
    </div>
</template>
<script>
import moment from "moment";
import CourseCompletionDetailsComponentVue from '../../CourseCompletion/CourseCompletionDetailsComponent.vue';
export default {
  props: ["detail"],
  data() {
    return {
      demoUser: window.demoUser,
      stud_info: {},
      course_payments: [],
      // course_invoice: [],
      edit_due_date : {},
      payment_details: {
        id: "",
        note: "",
        payment_date: "",
        amount: "",
        course_id: "",
        errors: [],
        edit: false,
      },
      invoice: {
        course_code: "",
        date: "",
        due_date: "",
        invoice_number: "",
      },
      errors: [],
      app_color: app_color,
      app_settings: window.app_settings,
      roles : null,
      // totalAmount: '',
      // balanceFee: '',
    };
  },
  computed: {
      balance : function (){
            let balance = 0
            if(this.detail != undefined){
                balance  =  this.detail.course_fee
            }
            return balance
      }
  },
  methods: {
    edit_due(detail) {
      detail.due_date = moment(detail.due_date)._d;
      this.edit_due_date['payment_'+detail.id] = detail;
    },
    closeOther(e,v){
        let collapseMenuBody = Array.from(document.getElementsByClassName('collapse-menu-body'));
        let collapseMenu = Array.from(document.getElementsByClassName('collapse-menu'));
        collapseMenuBody.map(element => {
             if(!element.className.includes(e)){
                  element.classList.remove('show')
              }
          })
        collapseMenu.map(element1 => {
             if(!element1.className.includes(v)){
                  element1.classList.remove('active')
              }else{
                  element1.classList.add('active')
              }
        })
    },
    savePayment(course_id) {
      swal.fire({
        title: "Please wait...",
        // html: '',// add html attribute if you want or remove
        allowOutsideClick: false,
        onBeforeOpen: () => {
          swal.showLoading();
        },
      });
      let vm = this;
      if (vm.payment_details.amount > parseInt(this.getBalanceFee())) {
        Toast.fire({
          type: "error",
          title: "Opss.. failed to add payment",
          position: "top-end",
        });
      } else {
        vm.payment_details.course_id = course_id;
        if (vm.payment_details.payment_date != null) {
          vm.payment_details.payment_date = moment(
            vm.payment_details.payment_date
          ).format("YYYY-MM-DD");
        }
        // console.log(vm.payment_details.payment_date);
        axios
          .post(
            `/student/domestic/${window.student}/payment-store`,
            this.payment_details
          )
          .then((response) => {
            if (response.data.status == "errors") {
              this.payment_details.errors = response.data.errors;
              Toast.fire({
                type: "error",
                title: "Opss.. failed to add payment",
                position: "top-end",
              });
            } else if (response.data.status == "success") {
              this.payment_details.errors = [];
              this.payment_details.id = "";
              this.payment_details.note = "";
              this.payment_details.payment_date = "";
              this.payment_details.amount = "";
              this.payment_details.course_id = "";
              this.payment_details.edit = false;
              this.success = true;
              Toast.fire({
                type: "success",
                title: "Data is saved successfully",
                position: "top-end",
              });
              this.$parent.fetchData();
            }
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
          });
      }
    },
  },
  filters : {
      dateFormat : function(data){
          return moment(data).format('DD/MM/YYYY');
      }
  }
}
</script>
<style scoped>
table.custom-table  th { background-color: transparent!important; color:gray!important; border: none!important;}
a.collapse-menu.active {
    background-color: #024b67;
    color: white;
}
.btn-square { border-radius: 0; }
.input-custom  {
    background-color: #7573731f;
    border:none;
    border-radius: 0;
}
</style>