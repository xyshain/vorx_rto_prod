<template>
    <div>
        <div class="float-left">
        <p>
                <a class="collapse-menu active p-1 pl-2 pr-2 m-1 paymentPlanH" type="button" data-toggle="collapse" @click="closeOther('paymentPlan','paymentPlanH')" href=".paymentPlan" aria-expanded="false" aria-controls="collapseExample">
                    <i class="fas fa-scroll"></i> Payment Plan
                </a>
                <a class="collapse-menu p-1 pl-2 pr-2 m-1 addPaymentH" type="button" data-toggle="collapse"  @click="closeOther('addPayment','addPaymentH')" href=".addPayment" aria-expanded="false" aria-controls="collapseExample1">
                    <i class="fas fa-plus"></i> Add Payment
                </a>
               
        </p>
        </div>
        <div class="float-right">
            <h5 v-show="menuchoicer == 'addPaymentH'">
                Remaining Balance: $ <span>{{ balance.toFixed(2) }}</span>
            </h5>
            <h5 v-show="menuchoicer == 'paymentPlanH'">
                Total Paid: $ <span>{{ paid.toFixed(2) }}</span>
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
                    <th class='text-center'>
                     
                    </th>
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
                    <td class='text-center'>
                      <div v-if=" remainingBalance[index] == 'danger'" >
                          <i class="fas fa-check-circle"></i>
                      </div>
                      <div v-else-if="remainingBalance[index] == 'bad'" >
                        <i class="fas fa-check-circle danger" style="color:red" data-toggle="tooltip" data-placement="top" title="partial payment below 50%"></i>
                      </div>
                      <div v-else-if="remainingBalance[index] == 'good'" >
                        <i class="fas fa-check-circle" style="color:yellow" data-toggle="tooltip" data-placement="top" title="partial payment above 50%"></i>
                      </div>
                      <div v-else >
                        <i class="fas fa-check-circle success" style="color:green" data-toggle="tooltip" data-placement="top" title="payment completed"></i>
                      </div>
                    </td>
                    <td class='text-center'>{{ index + 1 }}</td>
                    <td class='text-center'>{{ sched.payable_amount }}</td>
                    <td class='text-center'>{{ sched.due_date | dateFormat }}</td>
                    <td class='text-center'><div class="form-group mt-3">
                            <select class="form-control" id="exampleFormControlSelect1">
                            <option>Actions</option>
                            <option>Edit</option>
                            <option>Delete</option>
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
                        <input type="number"  v-model="payment_details.amount" min="0" class="form-control input-custom" id="amount" aria-describedby="emailHelp">
                    </div>
                </div>
                <div class="col-md">
                    <date-picker
                            v-model="payment_details.payment_date"
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
                        <input type="text" v-model="payment_details.note" class="form-control input-custom" id="notes" aria-describedby="emailHelp">
                    </div>
                </div>
                <div  v-if="payment_details.edit == true" class="col-md text-center ">
                    <div  class="form-group mt-4">
                        <button  @click="savePayment(detail.id)" class="btn btn-warning btn-square align-middle" style="width:100%"> <i class="fas fa-pen"></i> Update Payment </button>
                    </div>
                </div>
                <div  class="col-md text-center ">
                    <div  class="form-group mt-4">
                        <button v-if="payment_details.edit == false" @click="savePayment(detail.id)" class="btn btn-success btn-square align-middle" style="width:100%"> <i class="fas fa-plus"></i> Add Payment </button>
                        <button v-else @click="cancelUpdate" class="btn btn-danger btn-square align-middle" style="width:100%"> <i class="fas fa-plus"></i> Cancel Changes </button>
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
                <tr v-for="(sched,index) in detail.payment_details" :key="index"> 
                    <td class='text-center'>{{ sched.amount }}</td>
                    <td class='text-center'>{{ sched.payment_date | dateFormat }}</td>
                    <td class='text-center'>{{ sched.note}}</td>
                    <td class='text-center'>
                        <div class="form-group mt-3">
                            <select class="form-control"  @change="passAction(index,$event)" id="exampleFormControlSelect1">
                            <option value="">Actions</option>
                            <option value="Edit">Edit</option>
                            <option value="Delete">Delete</option>
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
      menuchoicer : 'paymentPlanH',
      // totalAmount: '',
      // balanceFee: '',
    };
  },
  computed: {
      balance : function (){
            let balance = 0
            if(this.detail != undefined){
                balance  =  this.detail.course_fee

                if(this.detail.payment_details.length > 0){
                    let payment_details = this.detail.payment_details;
                    let paid = payment_details.map(function(item){

                      return item.amount;
                    });
                    this.paid =  paid.reduce((a,b)=> parseInt(a)+ parseInt(b));
                    balance = balance - paid.reduce((a,b)=> parseInt(a)+ parseInt(b));
                }
            }
            return balance
      },
       remainingBalance: function () {
        var tempBalance = this.paid
        return this.detail.payment_sched.map((sched) => {
          tempBalance -= sched.payable_amount
          if(tempBalance >= 0){
            return 'complete';
          }else{
             if(Math.abs(tempBalance) > sched.payable_amount){
               return 'danger'
             }else{
                console.log(tempBalance);
                console.log(sched.payable_amount);
                let perHolder = (Math.abs(tempBalance) /  sched.payable_amount )* 100 ;
                perHolder = 100 - perHolder 
                if(perHolder < 50){
                  return 'bad'
                }else{
                  return 'good'
                }
             }
            
          }
        })
        // [900, 750, 635]
      }

      
  },
  methods: {
    passAction(data,event){
      let action = event.target.value
      let vm = this;
      if(action == 'Edit'){
        this.editPayment(data);
        event.target.value = ''
      }
      else if(action == 'Delete'){
        this.remove(data,this.detail.payment_details[data].id)
        event.target.value = ''
      }
    },
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
                  this.menuchoicer = v;
              }
        })
    },
    cancelUpdate(){
       this.payment_details =  {
        id: "",
        note: "",
        payment_date: "",
        amount: "",
        course_id: "",
        errors: [],
        edit: false,
      }
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
      if (vm.payment_details.amount > parseInt(this.balance)) {
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
    editPayment(item) {
      let vm = this;
      vm.payment_details.id = this.detail.payment_details[item].id;
      vm.payment_details.note = this.detail.payment_details[item].note;
      vm.payment_details.payment_date = moment(
        this.detail.payment_details[item].payment_date,
        "YYYY-MM-DD"
      )._d;
      vm.payment_details.amount = this.detail.payment_details[item].amount;
      vm.payment_details.edit = true;
    },
    remove(item, payment_detail_id) {
      swal
        .fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        })
        .then((result) => {
          let vm = this;
          if (result.value === true) {
            axios({
              method: "delete",
              url: `/student/domestic/${window.student}/delete/${payment_detail_id}`,
            })
              .then((res) => {
                // let i = this.course_payments[index].payment_details.map(item => item).indexOf(payment_detail_id) // find index of your object
                this.detail.payment_details.splice(item, 1);
                if (res.data.status == "success") {
                  Toast.fire({
                    type: "success",
                    title: "Data is deleted successfully",
                    position: "top-end",
                  });
                } else {
                  Toast.fire({
                    type: "error",
                    title: "Opss.. data was not deleted",
                    position: "top-end",
                  });
                }
              })
              .catch((err) => console.log(err));
          }
        });
    },
  },
  filters : {
      dateFormat : function(data){
          return moment(data).format('DD/MM/YYYY');
      },
      currencyDisplay: function(val){
          // read: function(val){
            return val.toFixed(2);
          // },
          // write: function(val){
            // var number = +val.replace(/[^\d.]/g,'');
            // return isNaN(number) ? 0 : number
          // }
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
.pie {
  width: 25px; height: 25px;
  border-radius: 50%;
  background: white;
  background-image: linear-gradient(to right, transparent 50%, red 0);
}

.pie::before {
  content: ’;
  display: block;
  margin-left: 50%;
  height: 100%;
  border-radius: 0 100% 100% 0 / 30%;
  background-color: inherit;
  rotate: 45%;
}


</style>