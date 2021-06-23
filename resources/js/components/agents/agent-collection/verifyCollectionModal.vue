<template>
    <modal
        name="verifyModal"
        transition="nice-modal-fade"
        classes="verify-collection"
        :min-width="200"
        :min-height="200"
        :pivot-y="0.1"
        :adaptive="true"
        :scrollable="true"
        :reset="true"
        width="40%"
        height="auto"
    >
        <div class="card card-body">
            <div v-if="data.payment_schedule_template_id !== null">
                
            </div>
            <span v-else class="text-center">
                Payment Schedule not found
            </span>
        </div>
    </modal>
</template>
<script>
export default {
    props:['data'],
    methods:{

    }
}
</script>