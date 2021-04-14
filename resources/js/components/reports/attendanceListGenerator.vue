<template>
  <div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 :class="'m-0 font-weight-bold text-'+app_color">Attendance List</h6>
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
                                    <div class="form-group col-md-2">
                                        <label for="year">Course:</label>
                                        <select name="" id="" class="form-control" v-model="get_course">
                                            <option :value="key" v-for="(itm,key) in courses" :key="key">
                                                <span v-if="key != '*'">{{key}} - {{itm}}</span>
                                                <span v-else>{{itm}}</span>
                                            </option>
                                        </select>
                                        <!-- <input type="number" class="form-control" id="year" placeholder=""> -->
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-5">
                                        <label for="month">Start Month: <span class="badge"><i>( Course start month )</i></span></label>
                                        <input type="month" class="form-control" v-model="from" value="">
                                        <!-- <select id="month" class="form-control">
                                            <option value="01-03">Jan - Mar</option>
                                            <option value="01-06">Jan - Jun</option>
                                            <option value="01-09">Jan - Sep</option>
                                            <option value="01-12">Jan - Dec</option>
                                        </select> -->
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="year">End Month: <span class="badge"><i>(Course end month)</i></span></label>
                                        <input type="month" class="form-control" v-model="to" value="">
                                        <!-- <input type="number" class="form-control" id="year" placeholder=""> -->
                                    </div>
                                    <div class="form-group col-md-2">
                                        <div class="clearfix" style="height: 22px;"></div>
                                        <button type="submit" @click="generateList" :class="'btn btn-'+app_color">Generate</button>
                                    </div>
                                </div>
                            </form>
                        </div>  
                    </div>
                </div>

                <!-- <v-client-table :data="courseList" :columns="columns" :options="options" ref="courseTable"></v-client-table> -->
                <v-client-table :class="'header-'+app_color" :data="student_list" :columns="columns" :options="options" ref="courseTable">
                        <div slot="afterLimit" class="ml-2">
                            <div class="btn-group">
                                <!-- <a href="javascript:void(0)"  @click="showCreateCourse" class="btn btn-success" slot="afterLimit"><i class="fas fa-plus"></i> Add Course</a> -->
                                <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Export to
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <!-- <a class="dropdown-item" href="#"><i class="far fa-file-pdf text-danger"></i>&nbsp; PDF</a> -->
                                    <a class="dropdown-item" href="#" @click="exportExcel"><i class="far fa-file-excel text-success"></i>&nbsp; Excel</a>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="btn-group" slot="actions" slot-scope="{row}">
                            <a href="javascript:void(0)" class="btn btn-primary btn-sm" @click="edit(row.id)"> <i class="fas fa-edit"> </i></a>
                            <a href="javascript:void(0)" class="btn btn-danger btn-sm text-white" @click="remove(row.id)"> <i class="fas fa-trash"> </i></a>
                        </div> -->
                </v-client-table>
            </div>
        </div>
    </div>
  </div>
</template>
<script>
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
      student_list: [],
      app_color: app_color,
      from: null,
      to: null,
      student_type: '*',
      get_course: '*',
      get_status: '*',
      get_agent: '*',
      statuses: [],
      agents: window.agents,
      courses: window.courses,
      columns: ["Student ID", "Given Name", "Last Name", "Date of Birth","USI","Course Code", "Course Name","Start Date", "End Date"],
      options: {
            initialPage:1,
            perPage:10,
            highlightMatches:true,
            sortIcon: { base:'fas', up:'fa-sort-amount-up', down:'fa-sort-amount-down', is:'fa-sort' },
            // headings: {
            //     id: '#',
            //     code: 'Course Code',
            //     name: 'Course Name',
            //     actions: 'Actions'
            // },
            sortable: ["Student ID", "Given Name", "Last Name", "Date of Birth","USI","Course Code", "Course Name","Start Date", "End Date"],
            rowClassCallback(row) {
                return row.id = row.id;
            },
            columnClasses: {id: 'class-is'},
            texts: {
                filter: "Search:",
                filterPlaceholder: "Search keywords",
            }
      },
    };
  },
  created() {
    // this.fetchStudents();
    this.filter_status();
  },
  methods: {
    
  }
};
</script>