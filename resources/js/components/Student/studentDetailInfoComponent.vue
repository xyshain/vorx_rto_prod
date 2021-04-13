<template>
  <div>
    <nav>
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a
          v-bind:class="'nav-item nav-link-'+app_color+' active'"
          id="personal-info-tab"
          data-toggle="tab"
          href="#personal-info"
          role="tab"
          aria-controls="personal-info"
          aria-selected="true"
        >Personal Information</a>
        <a
          v-bind:class="'nav-item nav-link-'+app_color"
          id="contact-details-tab"
          data-toggle="tab"
          href="#contact-details"
          role="tab"
          aria-controls="contact-details"
          aria-selected="true"
        >Contact Details</a>
        <a
          v-bind:class="'nav-item nav-link-'+app_color"
          id="visa-details-tab"
          data-toggle="tab"
          href="#visa-details"
          role="tab"
          aria-controls="contact-details"
          aria-selected="true"
        >Visa Details</a>

        <!-- student logins -->
        <a v-if="add_on.indexOf('student-portal') != -1" v-bind:class="'nav-item nav-link-'+app_color" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Logins</a>
      </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
      <div
        class="tab-pane fade show active"
        id="personal-info"
        role="tabpanel"
        aria-labelledby="personal-info-tab"
      >
        <form @submit.prevent autocomplete="nope">
        <div>
          <div class="row mb-3">
            <div class="col-md-12 pull-right text-right">
              <button class="btn btn-success" :disabled="roles == 'Staff'? true : false " @click="saveChanges()">
                <i class="far fa-save"></i> Save Changes
              </button>
            </div>
          </div>
          <div class="clearfix"></div>
          <div v-bind:class="'horizontal-line-wrapper-'+app_color+' mb-2'">
            <h6>Personal Information</h6>
          </div>
          <div class="clearfix"></div>
          <div class="form-padding-left-right">
            <div class="row">
              <!-- <div class="col-md-6">
                <div class="form-group">
                  <label for="student_type">Student Type</label>
                  <select id="student_type" v-model="student.student_type_id" class="form-control">
                    <option value="1">International</option>
                    <option value="2">Domestic</option>
                  </select>
                </div>
              </div> -->
              <div class="col-md-6">
                <div class="form-group">
                 <label for="student_type">Unique Student ID</label>
                <div class="input-group mb-3">
                <input type="text" v-model="student.unique_student_id" class="form-control" placeholder="Unique Student ID">
                <div class="input-group-append">
                    <button
                      v-if="student.verified"
                      class="btn btn-success btn-outline-secondary"
                      type="button"
                      @click="verifyUsiData"
                    >Verified</button>
                    <button
                      v-else
                      class="btn btn-outline-secondary"
                      type="button"
                      @click="verifyUsiData"
                    >Verify</button>
                  </div>
              </div>
              </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="prefix">Prefix</label>
                  <select id="studentPrefix" v-model="student.prefix" class="form-control">
                    <option value="Mr.">Mr.</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Mrs.">Mrs.</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="firstname">First Name</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="student.firstname"
                    id="firstname"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="middlename">Middle Name</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="student.middlename"
                    id="middlename"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="lastname">Last Name</label>
                  <input type="text" class="form-control" v-model="student.lastname" id="lastname" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <select id="gender" v-model="student.gender" class="form-control">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="date_of_birth">
                    Date of birth
                    <span style="font-size: 74%;opacity: 73%;">( MM/DD/YYYY )</span>
                  </label>
                  <date-picker
                    locale="en"
                    mode="date"
                    v-model="student.date_of_birth"
                    :masks="{L:'DD/MM/YYYY'}"
                    :input-props='{
                      autocomplete:"off",
                    }'
                  ></date-picker>
                </div>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-12">
                <div v-bind:class="'horizontal-line-wrapper-'+app_color+' mb-2'">
                  <h6>English Test</h6>
                </div>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>English Test</label>
                  <select @change="changeScoring()" v-model="englishTest" class="form-control">
                    <option
                      v-for="(test) in english_test"
                      :key="test.id"
                      :value="test.id"
                    >{{ test.name }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4" v-if="englishTest == 1 || englishTest == 4">
                <div class="form-group">
                  <label>English Test Score</label>
                  <select v-model="testScore" class="form-control">
                    <option v-for="(test) in testScoreRange" :key="test" :value="test">{{ test }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4" v-else-if="englishTest != '' && englishTest != 9">
                <div class="form-group">
                  <label>English Test Score</label>
                  <input type="text" class="form-control" v-model="testScore" />
                </div>
              </div>

              <div class="col-md-4" v-if=" englishTest != 9 || testScore != null">
                <div v-if="testScore !== ''">
                  <div class="form-group">
                    <label>English Test Date</label>
                    <date-picker
                      locale="en"
                      v-model="testDate"
                      :value="testDate"
                      :masks="{L:'DD/MM/YYYY'}"
                      :max-date="new Date()"
                    ></date-picker>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </form>
      </div>
      <div
        class="tab-pane fade"
        id="contact-details"
        role="tabpanel"
        aria-labelledby="contact-details-tab"
      >
        <div>
          <contact-details></contact-details>
        </div>
      </div>
      <div
        class="tab-pane fade show"
        id="visa-details"
        role="tabpanel"
        aria-labelledby="visa-details-tab"
      >
        <div>
          <visa-details></visa-details>
        </div>
      </div>
      <!-- student logins -->
      <div v-if="add_on.indexOf('student-portal') != -1" class="tab-pane fade show" id="login" role="tabpanel" aria-labelledby="login-tab">
          <div>
              <login></login>
          </div>
      </div>
    </div>
  </div>
</template>
<script>
import moment from "moment";
import VisaDetails from "./domestic/visaDetailsComponent.vue";
import ContactDetails from "./domestic/contactDetailsComponent";
import englishTest from "./offerLetter/offerLetterEnglishTestComponent.vue";
import Login from "./domestic/loginComponent.vue";
export default {
  components: {
    englishTest,
    VisaDetails,
    Login,
    ContactDetails,
  },
  mounted() {
      if(this.$root.$refs.studentbase != undefined){
      this.roles = this.$root.$refs.studentbase.urole;
    }
  },
  props: ["student"],
  created() {
  
    this.student.prefix = this.$parent.student.prefix;
    // this.student.student_type_id = this.$parent.student.student_type_id;
    this.student.firstname = this.$parent.student.firstname;
    this.student.middlename = this.$parent.student.middlename;
    this.student.lastname = this.$parent.student.lastname;
    this.student.gender = this.$parent.student.gender;
    this.student.unique_student_id = this.$parent.student.unique_student_id;
    this.student.verified = this.$parent.student.verified;
    this.student.date_of_birth = this.$parent.student.date_of_birth;
    if (window.englishData !== null) {
      this.englishTest = window.englishData.english_test_id;
      this.eng = window.englishData.english_test_id;
      this.testDate =
        window.englishData.test_date !== ""
          ? moment(window.englishData.test_date)._d
          : "";
    }
  },
  data() {
    return {
      app_color: app_color,
      add_on: add_on,
      englishTest: "",
      eng: "",
      testScore: "",
      testDate: "",
      testScoreRange: [],
      english_test: window.englishTest,
      roles : null,
    };
  },
  methods: {
    getAuth() {
      let apiUser = "admin";
      let apiPass = "nimda321";
      let basicAuth;
      basicAuth = "Basic " + btoa(apiUser + ":" + apiPass);
      return basicAuth;
    },
    verifyUsiData() {
      let vm = this;
      let apiBaseUrl = "https://usiapi.vorx.com.au:8443/api/";
      let usiForm = {
        usi: "",
        dateOfBirth: "",
        orgCode: window.app_settings.training_organisation_id,
      };
      console.log(vm.student);
      if (vm.student.unique_student_id == "" || vm.student.unique_student_id == null) {
         swal.fire({
          title: "Opss.. Unique Student ID must not be empty",
          type: "error",
          timer: 3000,
          showConfirmButton: false,
        });
      }else{
        usiForm.usi = vm.student.unique_student_id;
        if(vm.student.lastname == '' || vm.student.lastname == null){
            usiForm.singleName = vm.student.firstname;
        }else{
          usiForm.firstName =  vm.student.firstname;
          usiForm.familyName =  vm.student.lastname;
        }
         usiForm.dateOfBirth = moment(vm.student.date_of_birth).format("YYYY-MM-DD");
         let toast = swal.fire({
          position: "top-end",
          title: "Please wait",
          showConfirmButton: false,
          timer: 30000,
        });

        axios
          .post(`${apiBaseUrl}verify`, usiForm, {
            baseURL: window.location.host,
            headers: {
              Authorization: vm.getAuth(),
            },
          })
          .then((response) => {
            toast.dismiss === swal.DismissReason.timer;
            if (response.data.hasOwnProperty("errorInfo")) {
              console.log(response.data.errorInfo);
              let em = response.data.errorInfo;
              let rmessage = "";
              em.forEach((element) => {
                rmessage += "<li>" + element.message + "</li>";
              });

              swal.fire({
                type: "error",
                title: "Oops...",
                html: `<ul>${rmessage}</ul>`,
              });
            } else if (response.data.hasOwnProperty("code")) {
              let em = response.data.message;
              swal.fire({
                type: "error",
                title: "Oops...",
                html: em,
                footer: "<a href>Why do I have this issue?</a>",
              });
            } else {
              if (
                (response.data.firstName == "MATCH" &&
                  response.data.familyName == "MATCH" &&
                  response.data.dateOfBirth == "MATCH" &&
                  response.data.usistatus == "Valid") ||
                (response.data.singleName == "MATCH" &&
                  response.data.dateOfBirth == "MATCH" &&
                  response.data.usistatus == "Valid")
              ) {
                let sid = vm.student.student_id
                axios
                  .get(
                    `/usi/verify/success/${sid}/${usiForm.usi}`
                  )
                  .then((response) => {
                    vm.student.verified = 1;
                    // console.log(response);
                  })
                  .catch((err) => {
                    // console.log(err);
                  });
              }
              // console.log(response.data);
              swal.fire({
                title: "<strong>USI Result</strong>",
                type: "info",
                html: `<div class="info-message-wrapper">
              <div class="row">
                <div class="col-md-6 offset-md-3 text-left">
                  <span>First Name : ${response.data.firstName}</span>
                  <div class="clearfix"></div>
                  <span>Family Name : ${response.data.familyName}</span>
                  <div class="clearfix"></div>
                    <span>Single Name : ${response.data.singleName}</span>
                  <div class="clearfix"></div>
                    <span>Date of Birth : ${response.data.dateOfBirth}</span>
                  <div class="clearfix"></div>
                  <span>USI Status : ${response.data.usistatus}</span>
                  <div class="clearfix"></div>
                </div>
              </div>
            </div>`,
                showCloseButton: false,
                showCancelButton: false,
                focusConfirm: false,
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> Great!',
                confirmButtonAriaLabel: "Thumbs up, great!",
              });
            }
            // console.log(response)
          })
          .catch((err) => {
            // if (usiForm.dateOfBirth != "") {
            //   usiForm.dateOfBirth = moment(vm.usiForm.dateOfBirth)._d;
            // }
            swal.fire({
              type: "error",
              title: "Oops...",
              html: `<ul>${err.response.data.message}</ul>`,
            });
          });
        
        
      }
    },
    saveChanges() {
      const Toast = swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
      });
      swal.fire({
        title: "Please wait...",
        // html: '',// add html attribute if you want or remove
        allowOutsideClick: false,
        onBeforeOpen: () => {
          swal.showLoading();
        },
      });
      axios
        .put(`/student/${window.student}`, {
          prefix: this.student.prefix,
          firstname: this.student.firstname,
          middlename: this.student.middlename,
          lastname: this.student.lastname,
          gender: this.student.gender,
          // student_type_id: this.student.student_type_id,
          date_of_birth: moment(this.student.date_of_birth).format(
            "YYYY-MM-DD"
          ),
          englishTest: this.englishTest,
          testScore: this.testScore,
          testDateL:
            this.testDate !== ""
              ? moment(this.testDate).format("DD/MM/YYYY")
              : "",
        })
        .then((res) => {
          // console.log(res);
          if (res.data.status == "updated") {
            console.log("success");
            Toast.fire({
              type: "success",
              title: "Updated Successfuly",
            });
          } else {
            console.log("error");
            Toast.fire({
              type: "error",
              title: "Failed to Update",
            });
          }
        })
        .catch((err) => {
          console.log(err);
        });
    },
    changeScoring() {
      let scorerange = [];
      if (this.englishTest == 1) {
        for (let index = 4; index <= 9; index++) {
          scorerange.push(index.toFixed(1));
          if (index != 9) {
            scorerange.push(index + 0.5);
          }
        }
        this.testScoreRange = scorerange;
      } else if (this.englishTest == 4) {
        let scorerange = [
          "10-28",
          "29-35",
          "36-41",
          "42-49",
          "50-57",
          "58-64",
          "65-72",
          "73-78",
          "79-82",
          "83-85",
          "86-90",
        ];
        this.testScoreRange = scorerange;
      } else {
        // this.testScoreRange = scorerange;
        // this.testScoreRange = scorerange;
        this.testScoreRange = scorerange;
      }
      this.testScore = "";
    },
  },
  watch: {
    eng: function (newVal, oldVal) {
      console.log(newVal);
      this.changeScoring(newVal);
      this.testScore = window.englishData.score;
    },
  },
};
</script>
<style>
.tab-pane {
  border: 1px solid #e0dfdf;
  border-top: none;
  padding: 1.3rem;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  background-color: #ffffff;
}
.vc-text-base {
  display: block;
  width: 100%;
  height: calc(1.5em + 0.75rem + 2px);
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #6e707e;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #d1d3e2;
  border-radius: 0.35rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
</style>