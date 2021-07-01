<template>
    <modal
        name="viewTransactionModal"
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
            <div>
                <div class="card card-header text-center">
                View Transaction
                </div>
                <div class="card card-body">
                    <div class="card text-right"  style="border:none">
                        <p>
                             Amount : {{amount_paid.toFixed(2)}} 
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
                                <th width="20%">Payment Template #</th>
                                <th class='text-center' width="20%">Amount</th>
                                <th class='text-center' width="30%">Transaction #</th>
                                <th class='text-center' width="30%">Post Date</th>
                                <th class='text-center' width="30%">Agent</th>
                            </tr>
                         </thead>
                         <tbody>
                             <tr v-for="(st,index) in student_payment" :key="index">
                                 <td>{{st.payment_schedule_template_id}}</td>
                                 <td class="text-center">{{st.amount}}</td>
                                 <td class="text-center">{{st.transaction_code}}</td>
                                 <td class="text-center">{{st.payment_date | dateFormat}}</td>
                                 <td class="text-center">{{st.agent.agent_name}}</td>
                             </tr>
                         </tbody>
                    </table>                    
                </div>

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
        closed(){
            this.student_payment = [];
            this.amount_paid = 0;
        },
        opened(e){
            this.amount_paid = parseFloat(this.data.amount);
            this.getTransaction();
        },
        getTransaction(){
            axios.get(`/agent/get-transaction/${this.data.id}/${this.data.transaction_code}`).then(
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