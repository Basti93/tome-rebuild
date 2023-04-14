<template>
    <q-page>
        <div class="row q-ma-md">
            <div class="col-12">
                <q-card>
                    <q-card-section>
                        <div class="text-h6">Deine Trainings</div>
                        <div class="text-subtitle-1">Hier findest du alle deine Trainings</div>
                        <div class="text-subtitle-2">Du hast bereits an 43 Trainings in diesem Jahr teilgenommen</div>
                    </q-card-section>
                </q-card>
            </div>
            <single-training class="col-6" id="500"/>
        </div>
    </q-page>
</template>
<script>
import SingleTraining from "./SingleTraining.vue";

export default {
    components: {SingleTraining}

}
</script>