<template>
    <div class="card card-body"> 
        <table
            class="table custom-table"
            id="dataTable"
            width="100%"
            cellspacing="0"
        >
            <thead>
                <tr>
                    <th class='text-center'>Trxn Code</th>
                    <th class='text-center'>Student ID</th>
                    <th class='text-center'>Amount</th>
                    <th class='text-center'>Post Date</th>
                    <th class='text-center'>Notes</th>
                    <th class='text-center'>Attachment</th>
                    <th class='text-center'>Verified</th>
                    <!-- <th class='text-center'>Amount Paid</th> -->
                    <!-- <th class='text-center'>Post Date</th> -->
                    <th class='text-center'></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="pd in payment_details" :key="pd.id">
                    <td class="text-center">{{pd.transaction_code}}</td>
                    <td class="text-center">{{pd.student_id}}</td>
                    <td class="text-center">{{pd.amount}}</td>
                    <td class="text-center">{{pd.payment_date | dateFormat}}</td>
                    <td class="text-center">{{pd.note}}</td>
                    <td class="text-center">
                        <a :href="'/payment_attachment/'+pd.attachment.id"  v-if="pd.attachment !== null"><span class="fa fa-paperclip"></span></a>
                        <span v-else>
                            No attachment
                        </span>
                    </td>
                    <td class="text-center">
                        <span v-if="pd.verified === 1" >
                          <i class="fas fa-check-circle" style="color:green"></i>
                        </span>
                        <span v-else title="Pending">
                            <i class="fas fa-clock"></i>
                        </span>
                    </td>
                    <td class='text-center'>
                        <select @change="passActionPayment(index,$event)" class="form-control custominput" id="exampleFormControlSelect1">
                            <option value="">Actions</option>
                            <option value="Edit">Verify</option>
                            <option value="Delete">Decline</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import moment from 'moment'
export default {
    data(){
        return{
            app_color:app_color,
            agent_id:window.agent.id,
            payment_details:[]
        }
    },
    created(){
        this.getAgentCollections();
    },
    filters:{
        dateFormat : function(data){
          return moment(data).format('DD/MM/YYYY');
      },
    },
    methods:{
        getAgentCollections(){
            axios.get(`/agent/${this.agent_id}/collections`).then(
                response=>{
                    this.payment_details = response.data;
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