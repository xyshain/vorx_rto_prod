<template>
    <div>
        <div v-bind:class="'horizontal-line-wrapper-'+app_color+' my-3'">
            <h6>Payments</h6>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 pull-right text-right">
                <div class="btn-group">
                    <button class="btn btn-success">Remaining Balance : $ {{ remainingBalance }}</button>
                    <button class="btn btn-info" @click="pay" v-if="remainingBalance>0">Pay now</button>
                </div>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th :class="['background-'+app_color]">#</th>
                    <th :class="['background-'+app_color]">Date paid</th>
                    <th :class="['background-'+app_color]">Note</th>
                    <th :class="['background-'+app_color]">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(pd,index) in payment_details" :key="index">
                    <td>{{index+1}}</td>
                    <td>{{pd.payment_date | dateformat}}</td>
                    <td>{{pd.note}}</td>
                    <td>{{pd.amount}}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                <td colspan="3" align="right">Total :</td>
                <td colspan="1">$ {{ getTotal() }}</td>
                </tr>
            </tfoot>
        </table>

        <div v-bind:class="'horizontal-line-wrapper-'+app_color+' my-3'">
            <h6>Payment Schedule</h6>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th :class="['background-'+app_color]">#</th>
                    <th :class="['background-'+app_color]">Date</th>
                    <th :class="['background-'+app_color]">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(pc,index) in schedule_template" :key="index">
                    <td>{{index+1}}</td>
                    <td>{{pc.due_date | dateformat}}</td>
                    <td>{{pc.payable_amount}}</td>
                </tr>
            </tbody>
        </table>
        <pay-modal :course="course" :student="student" />
    </div>
</template>
<script>
import moment from "moment";
import payModal from "./payModal.vue";
export default {
    components:{
        payModal
    },
    props:['course'],
    data(){
        return{
            app_color:app_color,
            columns: ["payment_date","note","amount"],
            payment_details:[],
            student:window.student,
            schedule_template:[],
            options:{
                headings: {
                payment_date: "Date Paid",
                note: "Description",
                amount: "Amount",
                filterable:[]
                },
            },
            total_due:0,
            remainingBalance:0,
            amount:0,
        }
    },
    created(){
        this.getPayments();
    },
    filters:{
        dateformat: function(date) {
        if (!date) return "";
        date = moment(date).format("DD/MM/YYYY");
        return date;
        },
    },
    methods:{
        pay(){
            // this.$modal.show('pay-modal');
            var encoded_id = btoa(this.course.id)

            window.open("/student-portal/online-payment/"+encoded_id);
            // (async () => {
            
            // const { value: accept } = await swal.fire({
            // title: 'Online Payment',
            // width:400,
            // html:
            // `<div class="container"><div class="row justify-content-md-center"><input type="number" class="form-control col-md-5" value="${this.amount}" name="amount" min="0" step="0.01" placeholder="Amount in AUD"></div></div>`,
            // input: 'checkbox',
            // inputValue: 1,
            // inputPlaceholder:
            //     'Note: Service charge will be applied.',
            // confirmButtonText:
            //     'Continue&nbsp;<i class="fa fa-arrow-right"></i>',
            // inputValidator: (result) => {
            //     return !result && 'You need to agree with T&C'
            // }
            // })

            // if (accept) {
            // swal.fire('You agreed with T&C :)')
            // }

            // })()
            // location.href="/dashboard"
        },
        getTotal(){
            let vm = this;
            var sum = vm.payment_details
                .map((q) => parseInt(q.amount))
                .reduce(function (total, q) {
                return total + q;
                }, 0);
            return sum;
        },
        getPayments(){
            let id = 0;
            let dis = this;

            if(this.student.student_type_id==1){
                id = this.course.offer_letter_course_detail_id;
            }else{
                id = this.course.id;
            }
            // console.log(id);
            axios.get('/student_fees/get-fees/'+this.course.id).then(
                response=>{
                    var tot_due = 0;
                    var tot_paid = 0;

                    this.payment_details = response.data.payments;
                    this.schedule_template = response.data.schedule_template;
                    if(response.data.schedule_template!=''){
                        response.data.schedule_template.forEach(function(dis){
                            tot_due = tot_due + parseInt(dis.payable_amount);
                        });
                    }                    

                    if(response.data.payments!=''){
                        response.data.payments.forEach(function(dis){
                            tot_paid = tot_paid + parseInt(dis.amount);
                        });
                    }   
        
                    dis.total_due = tot_due;
                    dis.remainingBalance = tot_due-tot_paid;
                }
            ).catch(
                err=>{
                    console.log(err);
                }
            );
        }
    }
}
</script>
<style>
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style>