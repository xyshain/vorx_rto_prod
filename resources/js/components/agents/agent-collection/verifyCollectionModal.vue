<template>
    <modal
        name="verifyModal"
        transition="nice-modal-fade"
        classes="verify-collection"
        :min-width="800"
        :min-height="200"
        :pivot-y="0.1"
        :adaptive="true"
        :scrollable="true"
        :reset="true"
        width="40%"
        height="auto"
        style="padding-top:100px;"
        @opened="opened"
        @closed="closed"
    >
        
        <div class="card">
            <div v-if="data.payment_schedule_template_id !== null">
                <div class="card card-header text-center">
                Payment Schedule Template
                </div>
                <div class="card card-body">
                    <div class="card text-right"  style="border:none">
                        <p>
                            <span class="fas fa-circle " style="color:#f6c23e"></span> Amount : {{amount_paid.toFixed(2)}} 
                            <button class="btn btn-success btn-sm" @click="accept">Accept Payment</button>
                        </p>
                    </div>
                    <table
                        class="table custom-table"
                        id="dataTable"
                        width="100%"
                        cellspacing="0"
                    >
                         <thead>
                            <tr>
                                <th width="20%"></th>
                                <th class='text-center' width="20%">Order No</th>
                                <th class='text-center' width="30%">Amount Due</th>
                                <th class='text-center' width="30%">Amount Paid</th>
                                <th class='text-center' width="30%">Due Date</th>
                            </tr>
                         </thead>
                         <tbody>
                             <tr v-for="(st,index) in student_payment.funded_payment_sched_template" :key="index">
                                 <td class="text-center" :class="'bg-'+getBg(st.unverified_amount)">
                                     <!-- {{st.payable_amount}} - {{st.a}} -->
                                     <span v-if="parseFloat(st.approved_amount_paid) >= parseFloat(st.payable_amount)">
                                         <i class="fas fa-check-circle" style="color:green"></i>
                                     </span>
                                     <span v-else>
                                         {{parseFloat(st.unverified_amount).toFixed(2)}}
                                     </span>
                                 </td>
                                 <td class="text-center">{{index+1}}</td>
                                 <td class="text-center">{{st.payable_amount}}</td>
                                 <td class="text-center">{{st.approved_amount_paid}}</td>
                                 <td class="text-center">{{st.due_date | dateFormat}}</td>
                             </tr>
                         </tbody>
                    </table>                    
                </div>

            </div>
            <div v-else class="text-center card card-header">
                Payment Schedule not found
            </div>
        </div>
    </modal>
</template>
<script>
import moment from 'moment'
export default {
    props:['data'],
    data(){
        return{
            student_payment:[],
            amount_paid:0,
        }
    },
    filters:{
        dateFormat : function(data){
          return moment(data).format('DD/MM/YYYY');
        },
    },
    methods:{
        accept(){
            swal.fire({
                title: "Loading please wait...",
                // html: '',// add html attribute if you want or remove
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    swal.showLoading();
                },
            });
            let dataz = {
                student_payment:this.data,
                payment_schedule:this.student_payment.funded_payment_sched_template
            }
            axios.post(`/agent/collection/accept`,dataz).then(
                response=>{
                    if(response.data.status==='success'){
                        this.$parent.getAgentCollections();
                        this.$modal.hide('verifyModal');
                        Toast.fire({
                            position: "top-end",
                            type: "success",
                            title: 'Collection verified',
                        });
                    }else{
                        Toast.fire({
                            position: "top-end",
                            type: "error",
                            title: response.data.message,
                        });
                    }
                }
            );
        },
        getBg(unv_amount){
            if(typeof unv_amount!=='undefined' && unv_amount!=0){
                return 'warning'
            }
        },
        getAllocated(payable){
            let cash = this.amount_paid;
            console.log(cash);
            if(cash >= payable){
                return payable;
                this.amount_paid = this.amount_paid - payable;
            }else{
                this.cash;
            }
        },
        closed(){
            this.student_payment = [];
            this.amount_paid = 0;
        },
        opened(e){
            this.amount_paid = parseFloat(this.data.amount);
            this.getStudentPayments();
        },
        getStudentPayments(){
            axios.get(`/agent/student-payments/${this.data.student_course_id}/${this.amount_paid}`).then(
                response=>{
                    // console.log('gago');
                     this.student_payment = response.data;  
                }
            );
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
.input-custom{
  background-color: #7573731f;
  border:none;
  border-radius: 0;
}
.custominput {
  border:none;
  border-radius: 0;
  text-align-last: center;
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