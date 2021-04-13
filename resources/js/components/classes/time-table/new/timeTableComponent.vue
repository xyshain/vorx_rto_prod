<template>
  <div>
      <div class="row mb-2 d-flex justify-content-between">
        <div class="col-md-6">
            <a :href="'/classes'" v-bind:class="'btn btn-'+app_color+' text-primary btn-sm text-light'">
                <i class="fas fa-long-arrow-alt-left"></i> Back
            </a>
        </div>
      </div>
      <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Straight Time Table ({{getClass.desc}})</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6 text-right">
              <button class="btn btn-warning" v-if="is_save == 1" @click="resetTimeTable"><i class="fas fa-undo"></i> Reset</button>
              <!-- <button class="btn btn-warning" @click="autoFill(-1)"><i class="fas fa-list"></i> Generate</button> -->
              <button class="btn btn-success" @click="saveTimeTable"><i class="fas fa-save"></i> Save</button>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group" >
                  <!-- <label for="company_name"><span style="">Duration: <i v-if="getClass.time_table_type == 'Straight'">(Month)</i><i v-else>(Year)</i></span></label>
                  <input type="month" class="form-control" v-if="getClass.time_table_type == 'Straight'" @change="autoFill(-1)" v-model="tt.duration_start_date">
                  <select v-else name="" class="form-control" v-model="tt.duration_year">
                    <option :value="i" v-for="(i) in year_duration" :key="i">{{i}}</option>
                  </select> -->
                  <label for="start_date"><span style="">Class Start Date: </span></label>
                  <!-- <input type="month" class="form-control" v-if="getClass.time_table_type == 'Straight'" @change="autoFill(-1)" v-model="tt.duration_start_date"> -->
                  <date-picker 
                    mode='single'
                    :input-props='{
                      class: "vc-appearance-none vc-text-base vc-text-gray-800 vc-bg-white vc-border vc-border-gray-400 vc-rounded vc-w-full vc-py-2 vc-px-3 vc-leading-tight focus:vc-outline-none focus:vc-shadow",
                      readonly: true,
                      onchange: ""
                    }'
                    :masks="{ input: 'DD/MM/YYYY' }" 
                    locale="en" 
                    v-model="tt.duration_start_date" ></date-picker>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
                  <label for="company_name">Hours:
                  </label>
                  <input type="number" min="0" class="form-control" @change="autoFill(-1)" v-model="training_hours_daily">
              </div>
            </div>

            <div class="col-md-7">
              <div class="form-group">
                  <label class="w-100 mb-2" for="trainday">Training Days Per Week:
                  </label>
                    <div v-for="(i,k) in tt.training_days_weekly" :key="k" class="daydiv ml-2 float-left">
                        <input type="checkbox" class="chkbx" @change="autoFill(-1)" v-bind:id="k" v-model="tt.training_days_weekly[k]">
                        <span class="chklbl h5" v-bind:for="k">{{k}}</span>
                    </div>
                    <!-- <span v-for="(i,k) in tt.training_days_weekly" :key="k" class="custom-switch ml-2">
                        <input type="checkbox" class="custom-control-input" @change="autoFill(-1)" v-bind:id="k" v-model="tt.training_days_weekly[k]">
                        <label class="custom-control-label" v-bind:for="k">{{k}}</label>
                    </span> -->
              </div>
            </div>

            <!-- <div class="col-md-2 text-right mb-2 pt-3">
              <button class="btn btn-danger" @click="addBreak" v-if="getClass.time_table_type != 'Straight'"><i class="fas fa-plus"></i> Break</button>
              <button class="btn btn-warning" @click="autoFill(-1)" v-else><i class="fas fa-list"></i> Generate</button>
              <button class="btn btn-success" @click="saveTimeTable"><i class="fas fa-save"></i> Save</button>
            </div> -->
          </div>
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                    <th class="text-center" :class="['background-'+app_color]" width="5%" scope="col">#</th>
                    <th class="text-center" :class="['background-'+app_color]" width="10%" scope="col">Unit Code</th>
                    <th class="text-center" :class="['background-'+app_color]" width="25%" scope="col">Unit Title</th>
                    <th class="text-center" :class="['background-'+app_color]"  scope="col">Start / End Dates</th>
                    <!-- <th class="text-center" :class="['background-'+app_color]" scope="col">End Date</th> -->
                    <th class="text-center" :class="['background-'+app_color]" width="10%" scope="col">Training Hours</th>
                    <th class="text-center" :class="['background-'+app_color]" width="10%" scope="col">Weeks</th>
                    <th class="text-center" :class="['background-'+app_color]" scope="col">Trainers</th>
                    <!-- <th class="text-center" :class="['background-'+app_color]" scope="col">Holidays</th> -->
                    <th class="text-center" :class="['background-'+app_color]" scope="col">-</th>
                    </tr>
                </thead>
                <!-- <tbody> -->
                  <draggable v-model="tt.time_table" tag="tbody" @change="autoFill(-1)" @start="drag=true" @end="drag=false">
                    <tr v-for="(item, key) in tt.time_table" :key="key">
                        <td>{{item.order = key+1}}</td>
                        <td v-if="toType(item.is_break) !== 'undefined'" colspan="2" class="text-center"> Break </td>
                        <td v-if="toType(item.is_break) === 'undefined'">{{item.unit.code}}</td>
                        <td v-if="toType(item.is_break) === 'undefined'">{{item.unit.description}}</td>
                        <td>
                          <date-picker 
                            mode='range'
                            :input-props='{
                              class: "vc-appearance-none vc-text-base vc-text-gray-800 vc-bg-white vc-border vc-border-gray-400 vc-rounded vc-w-full vc-py-2 vc-px-3 vc-leading-tight focus:vc-outline-none focus:vc-shadow",
                              readonly:"readonly",
                              style: item.dateError == 1 ? "border: 1px solid red !important;" : "",
                            }'
                            :id="'dp-'+key"
                            :disabled-dates ="disabled_dates"
                            :masks="{ input: 'DD/MM/YYYY' }" 
                            locale="en" 
                            v-model="tt.time_table[key]['dates']" ></date-picker>
                            
                        </td>
                        <td v-if="toType(item.is_break) === 'undefined'"><input type="number" min="0" @change="autoFill(-1)" class="form-control" v-model="tt.time_table[key]['training_hours']"></td>
                        <td v-else></td>
                        <td><input type="number" min="0" readonly class="form-control" v-model="tt.time_table[key]['weeks']"></td>
                        
                        <!-- trainers -->
                        <td v-if="toType(item.is_break) === 'undefined'">

                          <multiselect
                            :class="'multiselect-color-'+app_color"
                            :options="trainers"
                            :multiple="false"
                            :custom-label="trainerLabel"
                            :close-on-select="true"
                            :select-label="''"
                            :searchable="false"
                            :limit="3"
                            :limit-text="limitText"
                            :max-height="600"
                            :show-no-results="false"
                            :hide-selected="true"
                            :id="'trainer-'+key"
                            v-model="tt.time_table[key]['trainer']"
                            placeholder="Select Trainer"
                          >
                          <span slot="noResult">Oops! No Unit found. Consider changing the search query.</span>
                          </multiselect>

                        </td>
                        <td v-else></td>

                        <td v-if="toType(item.is_break) !== 'undefined'" colspan="2" class="text-center"> <button class="btn btn-danger btn-sm" @click="removeBreak(key)"><i class="fas fa-minus"></i></button> </td>
                        <td class="text-center" v-else>
                          <span
                              v-if="toType(item.holidays) !== 'undefined' && item.holidays.length > 0"
                              :class="['a-'+app_color, 'holiday-info']"
                              @click="getHolidays(item.holidays)"
                          >
                              <i class="fas fa-info-circle fa-2x"></i>
                          </span>
                        </td>
                    </tr>
                  </draggable>
                    <!-- <tr v-for="(item, key) in timeTable.time_table" :key="key">
                      
                    </tr> -->
                <!-- </tbody> -->
                <tfoot>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <!-- <td></td> -->
                  <td class="text-right">{{tt.total_training_hours}}</td>
                  <td class="text-right">{{tt.total_weeks}}</td>
                  <td></td>
                  <td></td>
                </tfoot>
            </table>
            
        </div>
      </div>
  </div>
</template>


<script>
import moment from "moment";
import draggable from 'vuedraggable'
import endDatePicker from 'v-calendar/lib/components/date-picker.umd'

export default {
  name: "time-table",
  // mounted() {
  //     console.log('Component mounted.')
  // }
  components: {
    draggable,
    endDatePicker,
  },
  data() {
    return {
      app_color: app_color,
      getClass: {},
      trainers: window.trainers,
      is_save: 0,
      holidays: window.holidays,
      // year_duration: [], 
      // tt:  window.time_table,
      training_hours_daily: window.time_table.training_hours_daily,
      formatHolidays: [],
      tt: {},
      // class_start_day: 'Monday',
      disabled_dates: []
    };
  },
  filters: {
    dateformat: function(date) {
      if (!date) return "";
      date = moment(date).format("DD/MM/YYYY");
      return date;
    },
    timeformat: function(time){
        if (!time) return "";
        time = moment('1111-11-11 '+time).format("hh:mm A");
        return time;
    }
  },
  computed: {
    year_duration: function() {
      let years = [];
      for (let i = 1; i < 10; i++) {
        years.push(moment().add(i, 'years').format('YYYY'))
      }
      return years;
    }
  },
  created() {
    this.tt = window.time_table;
    this.getClass = window.class;
    this.is_save = window.is_save
    this.autoFill();
  },
  methods: {
    toType(obj) {
        return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    },
    getHolidays(arr) {
      console.log(arr)

      let html = '<ul style="margin-left: 10% !important;">';

      arr.forEach(v => {
          html += `<li style="text-align:left !important; color: #ff5757 !important;">${v.name} - ${v.date} (${v.weekday})</li>`;
      })
      html += '</ul><br><span><i>Note: If a holiday comes at scheduled day, the training will be moved to very next available day by default.<i></span>';

      swal.fire({
          type: 'warning',
          title: 'Holidays:',
          html: html
      })

    },
    resetTimeTable() {
      let vm = this;
      swal.fire({
        title: 'Reset Time Table?',
        text: "Saved time table will be erased.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
      }).then((result) => {
        if (result.value) {

          swal.fire({
              title: 'Resetting Time Table',
              // html: '',// add html attribute if you want or remove
              allowOutsideClick: false,
              onBeforeOpen: () => {
                  swal.showLoading()
              },
          });

          axios.get(`/classes/${vm.getClass.id}/time-table/reset`)
          .then(function(res) {

            if(res.data.status == 'success'){
              
              vm.tt = res.data.time_table;
              vm.is_save = 0
              swal.fire(
                'Success!',
                'Time table reset successfully',
                'success'
              )
            }

          })
          .catch(function(err) {
            console.log(err)
          })
          
        }
      })
    },
    trainerLabel({firstname, lastname}) {
      return firstname + ' ' + lastname;
    },
    limitText(count) {
      return `and ${count} other trainers`;
    },
    saveTimeTable() {
      let vm = this;
      swal.fire({
        title: 'Save time table?',
        html: "<i>Start and end date of the first and last unit will be the start and end date of the class.</i>",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
      }).then((result) => {
        if (result.value) {

            swal.fire({
                title: 'Saving time table...',
                // html: '',// add html attribute if you want or remove
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    swal.showLoading()
                },
            });

            axios.post('/classes/time-table',{
              class: vm.getClass,
              tt: vm.tt,
            })
            .then(function(res){
                // console.log(res.data);
                if(res.data.status == 'success'){
                    // window.location.href = '/online-enrolment/process/'+res.data.process;
                  vm.is_save = 1;
                  swal.fire(
                    'Success!',
                    'Time table saved successfully',
                    'success'
                  )
                }
            })
            .catch(function (err){
                // console.log(err);
                swal.fire(
                    'Oppss!',
                    'there seems to be a problem!',
                    'error'
                )
            })
            
        }
      })
    },
    addBreak(){
      let b = {
        is_break : 1,
        start_date: null,
        end_date: null,
        training_hours: null,
        weeks: null,
        order: null,
        holidays: []
      }
      this.tt.time_table.push(b); 
    },
    removeBreak(key){
      let tt = [];
      this.tt.time_table.forEach(element => {
        if(key+1 != element.order){
          tt.push(element);
        }
      });
      this.tt.time_table = tt;
    },
    totalTrainingHours(type){
      let total = 0;
      // console.log('click');
      this.tt.time_table.forEach(element => {
        if(type == 'th' && typeof element.training_hours !== 'undefined' && ['', null].indexOf(element.training_hours) == -1){
          if(typeof element.is_break === 'undefined'){
            total = parseInt(total) + parseInt(element.training_hours);
          }
        }
        if(type == 'wk' && typeof element.weeks !== 'undefined' && ['', null].indexOf(element.weeks) == -1){
          total = parseInt(total) + parseInt(element.weeks);
        }
      });
      // console.log(total);
      if(type == 'th'){
        this.tt.total_training_hours = Number.isNaN(total) ? 0 : total;
      }else{
        this.tt.total_weeks = Number.isNaN(total) ? 0 : total;
      }
    },
    fixDates(tt) {
      let start = null;
      let end = null;

      start = tt[0].dates.start
      end = tt[tt.length-1].dates.end

      tt.forEach(e => {
        e.dates = {
          start : start,
          end : end
        }
      })

      // console.log(start +' - '+ end)
      // console.log(tt[0])

    },
    autoFill() {
      
    //   swal.fire({
    //       title: 'Generating time table...',
    //       // html: '',// add html attribute if you want or remove
    //       allowOutsideClick: false,
    //       onBeforeOpen: () => {
    //           swal.showLoading()
    //       },
    //   });
    //   console.log('test');
      let dayStart = null;
      let vm = this;
      let table = [];
      let weekCount = 0;
      let disabled_dates = [];
      if(typeof vm.tt.duration_start_date === 'undefined' || [null, ''].indexOf(vm.tt.duration_start_date) != -1) {
          vm.tt.duration_start_date = moment(vm.getClass.start_date)._d;
      }
    //   console.log(vm.tt);

      let tt = vm.tt.time_table;

      let start = null;
      let run = null;
      let end = null;

      for (let index = 0; index < tt.length; index++) {
            // check training hours
            // tt[index].training_hours = tt[index].unit.scheduled_hours | 0;

            console.log(tt[index]);

            // console.log(tt[index].order);
            
            if(index == 0) {
                console.log('first');
                if(tt[index].training_hours == 0) {
                    console.log('break loop');
                    break;
                }
            }
      }



      
      
    //   vm.disabled_dates = disabled_dates;
    //   this.totalTrainingHours('th');
    //   this.totalTrainingHours('wk');
    //   this.fixDates(table)
    //   swal.close()
      
    },
    getDates(){
      this.tt.time_table.forEach(e => {
        let d = {};
        // console.log(e.dates);
        if(typeof e.dates !== 'undefined'){
          if(typeof e.dates.start !== 'undefined'){
            d.start = moment(e.dates.start)._d;
          }
          if(typeof e.dates.end !== 'undefined'){
            d.end = moment(e.dates.end)._d;
          }
        }
        e.dates = d;
      })
    }
  },
  watch: {
        tt : function (item){
        //   let data = [];
        //   let disabled_dates = [];
        //   item.time_table.forEach(e => {
        //     let th = 0;
        //     // if(typeof e.dates !== 'undefined'){
        //     //   if(typeof e.dates.start !== 'undefined'){
        //     //     e.dates.start = moment(e.dates.start)._d;
        //     //   }
        //     //   if(typeof e.dates.end !== 'undefined'){
        //     //     e.dates.end = moment(e.dates.end)._d;
        //     //   }
        //     // }
        //     // if(typeof e.end_date !== 'undefined'){
        //     //   e.end_date = moment(e.end_date)._d;
        //     // }

        //     if(typeof e.dates !== 'undefined' && typeof e.dates.start !== 'undefined' && typeof e.dates.end !== 'undefined'){
        //       disabled_dates.push({dates:{start:moment(e.dates.start)._d, end:moment(e.dates.end)._d}})
        //     }


        //     if(typeof e.unit !== 'undefined' && typeof e.training_hours === 'undefined'){
        //       th = e.unit.scheduled_hours
        //     }else if(typeof e.training_hours !== 'undefined'){
        //       th = e.training_hours
        //     }

        //     if(typeof e.weeks === 'undefined'){
        //       e.weeks = 0;
        //     }
        //     // e.totalTrainingHours;
        //     e.training_hours = th;
        //     data.push(e);
        //   })
        //   this.tt.time_table = data;
        //   this.disabled_dates = disabled_dates;
        //   this.totalTrainingHours('th');
        //   this.totalTrainingHours('wk');
        //   this.getDates();
        },

        getClass: function(i) {
          // console.log(i)
          let d = moment(i.start_date)._d;
          
          if(typeof this.tt.duration_start_date === 'undefined'){
            // console.log(d)
            this.tt.duration_start_date = d
            this.autoFill();
          }
          
        }
  },
};
</script>

<style scoped>
  .holiday-info {
    cursor: pointer !important;
  }
  .dateError {
    border: 1px solid red !important;
  }
  .chkbx {
    width: 25px;
    height: 17px;
  }
</style>