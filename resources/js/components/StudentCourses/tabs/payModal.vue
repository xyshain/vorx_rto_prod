<template>
    <modal
        name="pay-modal"
        transition="nice-modal-fade"
        classes="demo-modal-class"
        :min-width="200"
        :min-height="200"
        :pivot-y="0.1"
        :adaptive="true"
        :scrollable="true"
        :reset="true"
        width="40%"
        height="auto"
    >
    <div class="card card-header">
      <span><span style="font-size:20px;">Online Payment </span><span style="font-size:12px;"><i>(<b>student fields can be updated through <a href="/student-profile">student profile</a></b>)</i></span></span>
    </div>
    <div class="card card-body">
      <div>
        <a href="javascript:void(0)" class="btn btn-sm btn-info float-right" @click="getDetails()"><i class="fas fa-paste"></i> Use Details</a>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Receipt Email</label>
            <input type="email" class="form-control">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Name on Card</label>
            <input type="text" class="form-control">
          </div>
        </div>
        <div class="col-md-12">
          <div class="form-group">
            <label for="">Full Address</label>
            <input type="text" class="form-control">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Postal Code</label>
            <input type="text" class="form-control">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Phone</label>
            <input type="text" class="form-control">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for=""><span class="font-weight-bold">Amount
                    <a
                    href="#"
                    data-toggle="tooltip"
                    :class="'a-'+app_color"
                    :title="'Additional service fee will be charged.'"
                    data-placement="right"
                    >
                    <i class="fas fa-info-circle"></i>
                </a>
                </span> </label><span style="font-size: 74%;opacity: 73%;">( AUD )</span>
            
            <input type="number" class="form-control" name="amount" min="0" step="0.01" placeholder="AUD">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Credit/Debit Card</label>
            <div id="card-element">
                <card
                    :class="{complete}"
                    stripe="pk_test_51I7Bg3FU7cjv0OpgYeZ1COg6QrukVb8DqG7CmYTG7riv01SbFqTpbL0SeHVvg6qcy37q6cg2xHZBhSGTWlt4WLpZ007qEFk5xz"
                    :options="stripeOptions"
                    @change="change($event)"
                />
                <div id="card-errors" role="alert" v-text="errorMessage"></div>

            <!-- A Stripe Element will be inserted here. -->
            </div>
          </div>

          <!-- Used to display form errors. -->
          <div id="card-errors" role="alert"></div>
        </div>
        <div class="col-md-12">
          <button :class="'btn btn-success pull-right'" :disabled="payment_submit" @click="pay">
              <span v-if="payment_submit==false">Submit Payment</span>
              <span v-else><i class="fas fa-circle-notch fa-spin"></i> Please wait...</span>
          </button>
          <!-- <button :class="'btn btn-success pull-right'" :disabled="true" @click="pay"><i class="fas fa-circle-notch fa-spin"></i> Please wait...</button> -->
        </div>
      </div>
    </div>
    </modal>
</template>
<script>
import { Card, createToken } from 'vue-stripe-elements-plus'
export default {
    components:{
      Card
    },
    data(){
        return{
            app_color:app_color,
            accept_terms:false,
            user:user,
            errors:[],
            amount:'',
            payment_submit:false,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            complete:false,
            errorMessage:'',
            stripeOptions:{
                style:{
                    base: {
                        color: '#32325d',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                        color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    },
                },
                hidePostalCode:true
            },
        }
    },
    props:['course','student'],
    computed:{
      classObject:function(){
        if(this.accept_terms==false){
          return true
        }else{
          return false
        }
      }
    },
    methods:{
      getDetails(){
        
      },
      pay(){
        this.payment_submit = true;

        let dis = this;

        axios.get(`/student-portal/online-payment/stripe/getcustomer/${this.student.student_id}`).then(
          response=>{

          }
        ).catch(
          erro=>{
            Toast.fire({
              type: "success",
              title: erro,
              position: "top-end",
            });
          }
        )

        Toast.fire({
              type: "success",
              title: "Payment Success",
              position: "top-end",
            });
      },
      change(event){
          if (event.error) {
              this.errorMessage = event.error.message;
          } else {
              this.errorMessage = '';
          }
      },
      next(){
        // alert(this.csrf);
        if(this.amount==''||this.amount==0){
          this.errors = {
            amount:'The amount field is required'
          }
        }else{
          var encoded_id = btoa(this.course.id)
          // console.log(encoded_id);
          location.href="/student-portal/online-payment/"+encoded_id
        }
      }
    },
    watch:{
      amount:function(newVal){
        if(newVal!=''){
          this.errors = []
        }
      }
    },
    computed:{
      btnClass(){
        if(this.errorMessage!=''){
            return true
        }else{
            return false
        }
      }
    }
}
</script>
<style scoped>
.size-modal-content {
  padding: 10px;
  margin: 10px;
  font-style: 13px;
}
.v--modal-overlay[data-modal="size-modal"] {
  background: rgba(0, 0, 0, 0.5);
}
.demo-modal-class {
  border-radius: 5px;
  background: #f7f7f7;
  box-shadow: 5px 5px 30px 0px rgba(46, 61, 73, 0.6);
}

.StripeElement{
    border-color: rgba(0, 0, 0, 0.5) ;
    display: block;
    width: 100%;
    height: calc(1.6em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
    font-weight: 400;
    line-height: 1.6;
    color: #6e707e;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .StripeElement--invalid {
    border-color: #fa755a;
    }
    #card-errors{
        color: #fa755a;
    }
</style>