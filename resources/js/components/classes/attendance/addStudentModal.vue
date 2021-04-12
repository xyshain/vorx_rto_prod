<template>
  <modal
    name="add-student-modal"
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
    @before-open="beforeOpen"
    @opened="opened"
    @closed="closed"
    @before-close="beforeClose"
  >
    <div class="size-modal-content px-1">
      <h4 :class="'modal-header-'+app_color">Student Attendance</h4>
      <form @submit.prevent>
        <div class="row">
            <div class="col-md-12">
                <div :class="['form-group', errors.student_type ? 'has-error' : '']" >
                <label for="student_type">Student Type</label>
                  <select name="" id="" v-model="new_attendance.student_type" class="form-control" @change="typeChange" >
                    <option value="1">International</option>
                    <option value="2">Domestic</option>
                  </select>
                  <span v-if="errors.student_type" :class="['badge badge-danger']">{{ errors.student_type[0] }}</span>
                </div>
            </div>
            <div class="col-md-12">
                <div :class="['form-group', errors.student ? 'has-error' : '']" >
                <label for="deliver_loc">Student Name</label>
                 <multiselect v-model="new_attendance.student" 
                 :options="studentList" 
                 :custom-label="getStudentInfo"
                 :loading="isLoading"
                 :multiple="true"
                  :close-on-select="false"
                  placeholder="Select Student(s)" 
                  label="getStudentInfo" 
                  track-by="id"></multiselect>
                  <span v-if="errors.student" :class="['badge badge-danger']">{{ errors.student[0] }}</span>
                </div>
            </div>
        </div>
        <button :class="'btn btn-'+app_color+' btn-sm mt-3 float-right'" @click="saveAttendance">
          <i class="far fa-save"></i> Save
        </button>
        <div class="clearfix w-100"></div>
      </form>
    </div>
  </modal>
</template>
<script>
const Toast = swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000
});

export default {
  data() {
    return {
      app_color: app_color,
      errors: {},
      new_attendance:{
          student:'',
          class_date:'',
          class_id:window.class.id,
          student_type:''
      },
      studentList:[],
      isModal: "true",
      opt: [],
      isLoading: false
    };
  },
  watch: {
    fields: function(value) {
      if (value.code != null) {
        this.errors.code = "";
      }
      if (value.name != null) {
        this.errors.name = "";
      }
    }
  },
  mounted(){
    
  },
  methods: {
    typeChange(){
      this.new_attendance.student = '';
      this.isLoading = true;
      this.getStudents();
    },
    getStudentInfo({party,student_id}){
        return `${party.person.firstname?party.person.firstname:''} ${party.person.lastname?party.person.lastname:''} - ${student_id}`;
    },
    getStudents(){
        axios.get('/attendance/get_students/'+this.new_attendance.student_type+'/'+this.new_attendance.class_id).then(
            response=>{
                this.studentList = response.data;
                this.isLoading = false;
            }
        ).catch(
            err=>{
                console.log(error);
            }
        );
    },
    beforeOpen(e) {
      this.fields = {};
    },
    beforeClose(e) {
      this.new_attendance.student_type = '';
      this.isLoading = false;
    },
    opened(e) {
      // e.ref should not be undefined here
      // console.log('opened', e)
      // console.log('ref', e.ref)
      
    },
    closed(e) {
      this.errors = "";
      this.new_attendance.student='';
    },
    saveAttendance(){
      swal.fire({
        title: 'Please wait...',
        // html: '',// add html attribute if you want or remove
        allowOutsideClick: false,
        onBeforeOpen: () => {
          swal.showLoading()
        },
      });
      axios.post('/attendance/new_student_attendance',this.new_attendance).then(
          response=>{
            if(response.data.status=='error'){
              this.errors = response.data.errors;
              Toast.fire({
                type: "error",
                title: "Opss.. something went wrong",
                background: "#ffcdd2"
              });
            }else{
                Toast.fire({
                  // position: 'top-end',
                  type: "success",
                  title: "Data is saved successfully",
                  background: "#DCEDC8"
                });
                this.$modal.hide("add-student-modal");
                this.$parent.fetchAttendance();
                this.studentList= [];
            }
          }
      ).catch(
        e=>{
          console.log(e);
          Toast.fire({
            type: "error",
            title: "Opss.. something went wrong",
            background: "#ffcdd2"
            });
        }
      );
    }
  }
};
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