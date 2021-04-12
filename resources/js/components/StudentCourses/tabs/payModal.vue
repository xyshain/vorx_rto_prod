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
        width="30%"
        height="auto"
    >
    <div class="size-modal-content px-1">
      <h4 :class="'modal-header-'+app_color">Pay</h4>
      <form @submit.prevent id="formm" method="POST" action="/student-portal/online-payment">
      <input type="hidden" name="_token" :value="csrf">
        <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <div :class="['form-group', errors.amount ? 'has-error' : '']" >
                <label for="no_of_students">Amount:</label>
                <span style="font-size: 74%;opacity: 73%;">( AUD )</span>
                <input type="number" class="form-control" v-model="amount" name="amount" min="0" step="0.01" placeholder="AUD">
                <span v-if="errors.amount" :class="['badge badge-danger']">{{ errors.amount }}</span>
              </div>
              </div>
            </div>
            <div class="col-md-12" hidden>
              <div class="form-group">
                <label for="no_of_students">Student ID:</label>
                <input type="text" class="form-control" v-model="student.student_id" name="student_id" min="0" step="0.01" placeholder="AUD">
              </div>
            </div>
            <div class="col-md-12" hidden>
              <div class="form-group">
                <label for="no_of_students">Funded course id:</label>
                <input type="text" class="form-control" v-model="course.id" name="funded_course_id" min="0" step="0.01" placeholder="AUD">
              </div>
              </div>
              <div class="col-md-12" hidden>
              <div class="form-group">
                <label for="no_of_students">Offer letter course  detail id:</label>
                <input type="text" class="form-control" v-model="course.offer_letter_course_detail_id" name="offer_letter_course_detail_id" min="0" step="0.01" placeholder="AUD">
              </div>
              </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <p class="align-bottom"><input type="checkbox" class="" style="width: 35px;height: 20px;" v-model="accept_terms"><span>Service Fee will be charged</span></p>
                </div>
            </div>
            <div class="col-lg-4 offset-lg-4">
                <button type="button" :class="'btn btn-info '" :disabled="classObject" @click="next()"><i class="fa fa-arrow-right"></i>Continue</button>
            </div>
        </div>
      </form>
    </div>
    </modal>
</template>
<script>
export default {
    data(){
        return{
            app_color:app_color,
            accept_terms:false,
            errors:[],
            amount:'',
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
</style>