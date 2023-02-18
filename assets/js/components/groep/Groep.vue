<template>
  <div class="card groep">
    <div class="card-body">
      <h3 class="card-title">
        {{ naam }}
      </h3>
      <div class="row">
        <div v-if="!aangemeld && magAanmelden" class="left-col col-md-5">
          <p class="card-text">
            {{ samenvatting }}
          </p>
          <GroepAanmeldForm
            :keuzes="keuzelijst2"
            :opmerking="mijnOpmerking"
            :aangemeld="aangemeld"
            @aanmelden="aanmelden"
          />
        </div>
        <div class="col results">
          <table class="table table-sm">
            <GroepHeaderRow :keuzes="keuzelijst2" />
            <tbody>
              <GroepLidRow
                v-for="lid of leden"
                :key="lid.uid"
                :lid="lid"
                :keuzes="keuzelijst2"
              />
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import Vue, { PropType } from 'vue';
import {
  GroepInstance,
  GroepKeuzeSelectie,
  GroepLid,
  GroepSettings,
  KeuzeOptie,
} from '../../model/groep';
import GroepAanmeldForm from './GroepAanmeldForm.vue';
import GroepHeaderRow from './GroepHeaderRow.vue';
import GroepLidRow from './GroepLidRow.vue';

export default Vue.extend({
  components: { GroepAanmeldForm, GroepLidRow, GroepHeaderRow },
  props: {
    settings: {
      required: true,
      type: Object as PropType<GroepSettings>,
    },
    groep: {
      required: true,
      type: Object as PropType<GroepInstance>,
    },
  },
  data: () => ({
    id: 0,
    naam: '',
    familie: '',
    beginMoment: new Date(),
    eindMoment: new Date(),
    status: '',
    samenvatting: '',
    omschrijving: '',
    makerUid: '',
    versie: '',
    keuzelijst2: [] as KeuzeOptie[],
    leden: [] as GroepLid[],
    mijnUid: '',
    mijnLink: '',
    aanmeldUrl: '',
    mijnOpmerking: [] as GroepKeuzeSelectie[],
  }),
  computed: {
    mijnAanmelding() {
      return this.leden.find((lid) => lid.uid === this.mijnUid);
    },
    aangemeld() {
      return this.mijnAanmelding !== undefined;
    },
    magAanmelden() {
      console.log(this.groep.aanmeldenTot, new Date());
      if (this.groep.aanmeldenTot) {
        return new Date(this.groep.aanmeldenTot) > new Date();
      }

      return true;
    },
  },
  created() {
    this.id = this.groep.id;
    this.naam = this.groep.naam;
    this.familie = this.groep.familie;
    this.beginMoment = this.groep.beginMoment;
    this.eindMoment = this.groep.eindMoment;
    this.status = this.groep.status;
    this.samenvatting = this.groep.samenvatting;
    this.omschrijving = this.groep.omschrijving;
    this.makerUid = this.groep.makerUid;
    this.versie = this.groep.versie;
    this.keuzelijst2 = this.groep.keuzelijst2;
    this.leden = this.groep.leden;

    this.mijnUid = this.settings.mijn_uid;
    this.mijnLink = this.settings.mijn_link;
    this.aanmeldUrl = this.settings.aanmeld_url;

    if (this.aangemeld) {
      this.mijnOpmerking = this.mijnAanmelding.opmerking2;
    } else {
      this.mijnOpmerking = this.keuzelijst2.map((value) => ({
        selectie: value.default,
        naam: value.naam,
      }));
    }
  },
  methods: {
    aanmelden() {
      if (!this.aangemeld) {
        this.leden.push({
          uid: this.mijnUid,
          link: this.mijnLink,
          opmerking2: this.mijnOpmerking,
        });

        axios.post(this.aanmeldUrl, { opmerking2: this.mijnOpmerking });
      }
    },
  },
});
</script>

<style scoped>
.left-col {
  border-right: 1px solid rgba(0, 0, 0, 0.125);
}

.groep {
  min-height: 300px;
}

.results {
  overflow: auto;
}
</style>
