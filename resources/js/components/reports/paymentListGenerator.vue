<template>
  <div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 :class="'m-0 font-weight-bold text-'+app_color">Payment List</h6>
         </div>
        <div class="card-body">
            <div>
                <!-- <div v-bind:class="'horizontal-line-wrapper-'+app_color+' mb-2'">
                    <h6>Date Range</h6>
                </div> -->
                <div class="clearfix"></div>
                <div class="form-padding-left-right">
                    <div class="row">
                        <div class="col-md-12">
                            <form @submit.prevent>
                                <div class="form-row">
                                    <div class="form-group col-md-5">
                                        <label for="month">Start date: <span class="badge"></span></label>
                                        <date-picker :masks="{ L: 'DD/MM/YYYY' }" locale="en" v-model="from" ></date-picker>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="year">End date: <span class="badge"></span></label>
                                        <date-picker :masks="{ L: 'DD/MM/YYYY' }" locale="en" v-model="to" ></date-picker>
                                        <!-- <input type="number" class="form-control" id="year" placeholder=""> -->
                                    </div>
                                    <div class="form-group col-md-2">
                                        <div class="clearfix" style="height: 22px;"></div>
                                        <button @click="resetFields" :class="'btn btn-warning'" title="Reset Fields"><i class="fa fa-rotate-left"></i></button>
                                        <button type="submit" @click="generateList" :class="'btn btn-'+app_color">Generate</button>
                                    </div>
                                </div>
                            </form>
                        </div>  
                    </div>
                </div>
                
            </div>
        </div>
    </div>
  </div>
</template>
<script>
import moment from 'moment'
// import CreateOfferLetter from "./createOfferLetterComponent.vue";
export default {
  name: "app-modal",
  mounted() {
    // console.log("hello");
  },
  components: {
    // CreateOfferLetter
  },
  data() {
    return {
        from:null,
        to:null,
        app_color:app_color
    }
  },
  created() {
    // this.fetchStudents();
  },
  methods: {
      generateList(){
          
      },
      exportExcel(){
          
        
      },
      resetFields(){
          console.log();
          this.selected_class = '';
          this.student_type = '*';
          this.from = null;
          this.to = null;
      }
  },
};
</script>
