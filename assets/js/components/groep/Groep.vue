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
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
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

// noinspection JSUnusedGlobalSymbols
@Component({ components: { GroepAanmeldForm, GroepLidRow, GroepHeaderRow } })
export default class Groep extends Vue {
  /// Props
  @Prop()
  settings: GroepSettings;
  @Prop()
  groep: GroepInstance;

  /// Data
  id = 0;
  naam = '';
  familie = '';
  beginMoment: Date = new Date();
  eindMoment: Date = new Date();
  status = '';
  samenvatting = '';
  omschrijving = '';
  makerUid = '';
  versie = '';
  keuzelijst2: KeuzeOptie[] = [];
  leden: GroepLid[] = [];
  mijnUid = '';
  mijnLink = '';
  aanmeldUrl = '';
  mijnOpmerking: GroepKeuzeSelectie[] = [];

  private created() {
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
  }

  /// Getters
  private get mijnAanmelding() {
    return this.leden.find((lid) => lid.uid === this.mijnUid);
  }

  private get aangemeld() {
    return this.mijnAanmelding !== undefined;
  }

  private get magAanmelden() {
    console.log(this.groep.aanmeldenTot, new Date());
    if (this.groep.aanmeldenTot) {
      return new Date(this.groep.aanmeldenTot) > new Date();
    }

    return true;
  }

  /// Methods
  private aanmelden() {
    if (!this.aangemeld) {
      this.leden.push({
        uid: this.mijnUid,
        link: this.mijnLink,
        opmerking2: this.mijnOpmerking,
      });

      axios.post(this.aanmeldUrl, { opmerking2: this.mijnOpmerking });
    }
  }
}
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
