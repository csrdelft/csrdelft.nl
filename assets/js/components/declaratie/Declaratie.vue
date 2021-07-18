<template>
  <div class="declaratie">
    <div class="voortgang">
      <div
        class="fase concept"
        :class="{'active': declaratie.status === 'concept', 'done': declaratie.status !== 'concept'}"
      >
        <span class="status">Concept</span>
      </div>
      <div
        class="fase ingediend"
        :class="{'active': declaratie.status === 'ingediend', 'done': declaratie.status === 'goedgekeurd' || declaratie.status === 'afgekeurd' || declaratie.status === 'uitbetaald'}"
      >
        <span class="status">Ingediend</span>
        <span class="datum">{{ declaratie.statusDatums.ingediendOp }}</span>
      </div>
      <div
        v-if="declaratie.status !== 'afgekeurd'"
        class="fase goedgekeurd"
        :class="{'active': declaratie.status === 'goedgekeurd', 'done': declaratie.status === 'uitbetaald'}"
      >
        <span class="status">Goedgekeurd</span>
        <span class="datum">{{ declaratie.statusDatums.goedgekeurdOp }}</span>
      </div>
      <div
        v-if="declaratie.status === 'afgekeurd'"
        class="fase uitbetaald"
        :class="{'active': declaratie.status === 'afgekeurd'}"
      >
        <span class="status">Afgekeurd</span>
        <span class="datum">{{ declaratie.statusDatums.afgekeurdOp }}</span>
      </div>
      <div
        v-if="declaratie.status !== 'afgekeurd' && declaratie.betaalwijze === 'voorgeschoten'"
        class="fase concept"
        :class="{'active': declaratie.status === 'uitbetaald'}"
      >
        <span class="status">Uitbetaald</span>
        <span class="datum">{{ declaratie.statusDatums.uitbetaaldOp }}</span>
      </div>
    </div>

    <div class="field">
      <label for="categorie">Categorie</label>
      <select
        id="categorie"
        v-model="declaratie.categorie"
        :disabled="veldenDisabled"
      >
        <option disabled />
        <option
          v-for="(categorie,categorieID) in categorieen"
          :key="'categorie-' + categorieID"
          :value="categorieID"
        >
          {{ categorie }}
        </option>
      </select>
    </div>

    <div class="field">
      <label>Omschrijving declaratie</label>
      <input
        id="omschrijving"
        v-model="declaratie.omschrijving"
        type="text"
        :disabled="veldenDisabled"
        maxlength="255"
      >
    </div>

    <div class="field">
      <label>Betaalwijze</label>
      <div>
        <input
          id="C.S.R.-pas"
          v-model="declaratie.betaalwijze"
          type="radio"
          value="C.S.R.-pas"
          :disabled="veldenDisabled"
        >
        <label for="C.S.R.-pas">Betaald met C.S.R.-pas</label>
      </div>
      <div>
        <input
          id="voorgeschoten"
          v-model="declaratie.betaalwijze"
          type="radio"
          value="voorgeschoten"
          :disabled="veldenDisabled"
        >
        <label for="voorgeschoten">Voorgeschoten</label>
      </div>
    </div>

    <div
      v-if="declaratie.betaalwijze === 'voorgeschoten'"
      class="field"
    >
      <label>Terugstorten</label>
      <div>
        <input
          id="eigenRekening"
          v-model="declaratie.eigenRekening"
          type="radio"
          :value="true"
          :disabled="veldenDisabled"
        >
        <label for="eigenRekening">Naar eigen rekening: {{ iban }} t.n.v. {{ tenaamstelling }}</label>
      </div>
      <div>
        <input
          id="nietEigenRekening"
          v-model="declaratie.eigenRekening"
          type="radio"
          :value="false"
          :disabled="veldenDisabled"
        >
        <label for="nietEigenRekening">Naar andere rekening</label>
      </div>
    </div>

    <div
      v-if="declaratie.betaalwijze === 'voorgeschoten' && !declaratie.eigenRekening"
      class="field"
    >
      <label for="rekening">IBAN</label>
      <input
        id="rekening"
        v-model="declaratie.rekening"
        type="text"
        :disabled="veldenDisabled"
        maxlength="255"
      >
    </div>

    <div
      v-if="declaratie.betaalwijze === 'voorgeschoten' && !declaratie.eigenRekening || declaratie.betaalwijze === 'C.S.R.-pas'"
      class="field"
    >
      <label
        v-if="declaratie.betaalwijze === 'voorgeschoten'"
        for="tnv"
      >Ten name van</label>
      <label
        v-else
        for="tnv"
      >Bij bedrijf</label>
      <input
        id="tnv"
        v-model="declaratie.tnv"
        type="text"
        :disabled="veldenDisabled"
        maxlength="255"
      >
    </div>

    <div
      v-if="bonUploaden || !heeftBonnen"
      class="bonnen bon-upload"
    >
      <div class="inhoud">
        <div class="titel">
          Voeg je bonnen en facturen toe
        </div>
        <p>
          Upload je bon of factuur als PDF of goed leesbare foto.
          Neem daarna de bedragen van de bon of het factuur over.
        </p>
        <div class="buttons">
          <button
            v-if="uploading"
            class="loading blue"
            disabled
          >
            <i class="fas fa-circle-notch fa-spin" />
          </button>
          <template v-else>
            <label
              class="blue"
              for="fileUpload"
            >
              Kies bestand
            </label>
            <input
              id="fileUpload"
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              @change="uploadBon($event.target.files)"
            >
            <button
              v-if="heeftBonnen"
              class="open"
              @click="bonUploaden = false"
            >
              Annuleren
            </button>
          </template>
        </div>
      </div>
    </div>

    <div
      v-if="!bonUploaden && heeftBonnen"
      class="bonnen bonnen-weergave"
    >
      <div class="lijst">
        <div
          v-for="(bon,bonIndex) in declaratie.bonnen"
          :key="'bon-' + bonIndex"
          class="bon"
        >
          <div
            v-if="bonIndex !== geselecteerdeBon"
            class="bon-collapsed"
            @click="geselecteerdeBon = bonIndex"
          >
            <div class="left">
              <div class="title">
                Bon {{ bonIndex + 1 }}
              </div>
              <div class="date">
                {{ bon.datum }}
              </div>
            </div>
            <div class="right">
              <div class="title">
                &euro; {{ berekening(bon).totaalIncl|bedrag }}
              </div>
              <div class="btw">
                incl. btw
              </div>
            </div>
          </div>
          <div
            v-else
            class="bon-selected"
          >
            <div
              v-if="declaratie.bonnen.length > 1 && !veldenDisabled"
              class="bonVerwijderen"
              @click="bonVerwijderen(bonIndex)"
            >
              <i class="fa fa-trash-alt" />
            </div>
            <div class="title">
              Bon {{ bonIndex + 1 }}
            </div>

            <div class="field">
              <label :for="'bon' + bonIndex + '_datum'">Datum</label>
              <input
                :id="'bon' + bonIndex + '_datum'"
                v-model="bon.datum"
                v-input-mask
                data-inputmask="'alias': 'datetime', 'inputFormat': 'dd-mm-yyyy'"
                type="text"
                :disabled="veldenDisabled"
              >
            </div>

            <div class="bon-regels">
              <div class="regels-row">
                <label>Omschrijving</label>
                <label>Bedrag</label>
                <label>Btw</label>
                <div />
              </div>
              <div
                v-for="(regel, index) in bon.regels"
                :key="'regel-' + bonIndex + '-' + index"
                class="regels-row"
              >
                <div class="field">
                  <input
                    v-model="regel.omschrijving"
                    type="text"
                    :disabled="veldenDisabled"
                    maxlength="255"
                  >
                </div>
                <div class="field">
                  <money
                    v-model="regel.bedrag"
                    v-money="money"
                    style="text-align: right;"
                    :disabled="veldenDisabled"
                  />
                </div>
                <div class="field">
                  <select
                    v-model="regel.btw"
                    :disabled="veldenDisabled"
                  >
                    <option
                      value=""
                      disabled
                    />
                    <option value="incl. 9%">
                      incl. 9%
                    </option>
                    <option value="incl. 21%">
                      incl. 21%
                    </option>
                    <option value="excl. 9%">
                      excl. 9%
                    </option>
                    <option value="excl. 21%">
                      excl. 21%
                    </option>
                    <option value="geen: 0%">
                      geen: 0%
                    </option>
                  </select>
                </div>
                <div
                  v-if="bon.regels.length > 1 && !veldenDisabled"
                  class="trash"
                  @click="regelVerwijderen(bon, index)"
                >
                  <i class="fa fa-trash-alt" />
                </div>
              </div>
              <div
                v-if="!veldenDisabled"
                class="regels-row nieuw"
                @click="nieuweRegel(bon)"
              >
                <div class="field">
                  <input
                    type="text"
                    disabled
                  >
                </div>
                <div class="field">
                  <input
                    type="text"
                    disabled
                  >
                </div>
                <div class="field">
                  <select disabled>
                    <option value="" />
                  </select>
                </div>
                <div class="add">
                  <i class="fa fa-plus-circle" />
                </div>
              </div>
              <div class="regels-row totaal streep">
                <div class="onderdeel">
                  Totaal excl. btw
                </div>
                <div class="bedrag">
                  {{ berekening(bon).totaalExcl|bedrag }}
                </div>
              </div>
              <div
                v-if="berekening(bon).btw[9] !== 0"
                class="regels-row totaal"
              >
                <div class="onderdeel">
                  Btw 9%
                </div>
                <div class="bedrag">
                  {{ berekening(bon).btw[9]|bedrag }}
                </div>
              </div>
              <div
                v-if="berekening(bon).btw[21] !== 0"
                class="regels-row totaal"
              >
                <div class="onderdeel">
                  Btw 21%
                </div>
                <div class="bedrag">
                  {{ berekening(bon).btw[21]|bedrag }}
                </div>
              </div>
              <div class="regels-row totaal totaalBold">
                <div class="onderdeel">
                  Totaal incl. btw
                </div>
                <div class="bedrag">
                  {{ berekening(bon).totaalIncl|bedrag }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          v-if="!veldenDisabled"
          class="nieuwe-bon"
          @click="bonUploaden = true"
        >
          <i class="fa fa-plus-circle" />
        </div>
      </div>
      <div class="voorbeeld">
        <iframe
          v-for="(bon,bonIndex) in declaratie.bonnen"
          v-show="geselecteerdeBon === bonIndex"
          :key="'voorbeeld-' + bonIndex"
          :src="bon.bestandsnaam"
        />
      </div>
    </div>

    <div
      v-if="totaal > 0"
      class="totaal"
    >
      <div class="left">
        Totaal
      </div>
      <div class="right">
        <div class="title">
          &euro; {{ totaal|bedrag }}
        </div>
        <div class="btw">
          incl. btw
        </div>
      </div>
    </div>

    <div class="field">
      <label for="opmerkingen">Opmerkingen</label>
      <textarea
        id="opmerkingen"
        v-model="declaratie.opmerkingen"
        :disabled="veldenDisabled"
      />
    </div>

    <div
      v-for="(error, index) in errors"
      :key="'error-' + index"
      class="field alert alert-warning"
      role="alert"
    >
      {{ error }}
    </div>

    <div
      v-if="!veldenDisabled || submitting"
      class="save-buttons"
    >
      <button
        class="concept"
        :disabled="submitting"
        @click="declaratieOpslaan(false)"
      >
        Concept opslaan en later afmaken
      </button>
      <button
        class="confirm"
        :disabled="submitting"
        @click="declaratieOpslaan(true)"
      >
        Declaratie indienen
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import Vue from 'vue';
import {Component, Prop} from 'vue-property-decorator';

type status = 'concept' | 'ingediend' | 'afgekeurd' | 'goedgekeurd' | 'uitbetaald';

interface StatusDatums {
  ingediendOp?: string;
  goedgekeurdOp?: string;
  afgekeurdOp?: string;
  uitbetaaldOp?: string;
}

interface Declaratie {
  id?: number;
  categorie?: number;
  omschrijving?: string;
  betaalwijze?: 'C.S.R.-pas' | 'voorgeschoten';
  eigenRekening?: boolean;
  rekening?: string;
  tnv?: string;
  bonnen?: Bon[];
  opmerkingen: string;
  status: status;
  statusDatums?: StatusDatums;
}

interface Bon {
  bestandsnaam: string;
  datum: string;
  regels: Regel[];
}

interface Regel {
  omschrijving: string;
  bedrag: number | null;
  btw: '' | 'incl. 9%' | 'incl. 21%' | 'excl. 9%' | 'excl. 21%';
}

const legeRegel: () => Regel = () => ({
  omschrijving: '',
  bedrag: 0,
  btw: '',
});

const legeBon: (string, number) => Bon = (bon, id) => ({
  id: id,
  bestandsnaam: bon,
  datum: '',
  regels: [legeRegel()],
});

const legeDeclaratie: () => Declaratie = () => ({
  opmerkingen: '',
  eigenRekening: true,
  status: 'concept',
  statusDatums: {
    uitbetaaldOp: '',
    goedgekeurdOp: '',
    afgekeurdOp: '',
    ingediendOp: '',
  },
  bonnen: [],
});

interface DeclaratieOpslaanResponse {
  data: DeclaratieOpslaanData
}

interface DeclaratieOpslaanData {
  id?: number
  messages: string[]
  success: boolean
  status: status
  statusDatums: StatusDatums
}

@Component({
  filters: {
    bedrag(value: number) {
      const text = value.toString();
      const split = text.split('.');
      if (split.length === 1) {
        return text + ',00';
      } else {
        return split[0] + ',' + split[1].padEnd(2, '0');
      }
    },
  },
})
export default class DeclaratieVue extends Vue {
  @Prop()
  private type: 'nieuw' | 'bewerken';
  @Prop()
  private categorieen: Record<number, string>;
  @Prop({default: legeDeclaratie})
  private declaratieinput: Declaratie;
  @Prop()
  private iban: string;
  @Prop()
  private tenaamstelling: string;

  private declaratie = this.declaratieinput;
  private bonUploaden = this.declaratie.bonnen.length === 0;
  private uploading = false;
  private geselecteerdeBon = 0;
  private money = {precision: 2, decimal: ',', thousands: ' ', prefix: '€ '};
  private submitting = false;
  private errors = [];

  private get veldenDisabled() {
    return this.submitting || this.declaratie.status !== 'concept';
  }

  private get heeftBonnen() {
    return this.declaratie.bonnen && this.declaratie.bonnen.length > 0;
  }

  public nieuweBon(file: string, id: number): void {
    this.declaratie.bonnen.push(
      legeBon(file, id)
    );
    this.geselecteerdeBon = this.declaratie.bonnen?.length - 1;
  }

  public bonVerwijderen(index: number): void {
    this.declaratie.bonnen.splice(index, 1);
  }

  public nieuweRegel(bon: Bon): void {
    bon.regels.push(legeRegel());
  }

  public regelVerwijderen(bon: Bon, regel: number): void {
    bon.regels.splice(regel, 1);
  }

  public get totaal(): number {
    let totaal = 0;
    for (let bon of this.declaratie.bonnen) {
      totaal += this.berekening(bon).totaalIncl;
    }
    return totaal;
  }

  public berekening(bon: Bon): { totaalExcl: number, totaalIncl: number, btw: { 0: number, 9: number, 21: number } } {
    const ret = {
      totaalExcl: 0,
      totaalIncl: 0,
      btw: {
        0: 0,
        9: 0,
        21: 0,
      },
    };

    for (const regel of bon.regels) {
      if (regel.btw && regel.bedrag) {
        regel.bedrag = parseFloat(regel.bedrag.toString());
        const incl = regel.btw.substr(0, 4) === 'incl';
        const percentage = parseInt(regel.btw.substr(6).replace('%', ''), 10);
        const perunage = percentage / 100;

        if (incl) {
          ret.totaalExcl += regel.bedrag / (1 + perunage);
          ret.btw[percentage] += regel.bedrag / (1 + perunage) * perunage;
          ret.totaalIncl += regel.bedrag;
        } else {
          ret.totaalExcl += regel.bedrag;
          ret.btw[percentage] += regel.bedrag * perunage;
          ret.totaalIncl += regel.bedrag * (1 + perunage);
        }
      }
    }

    function round(toRound: number) {
      return Math.round((toRound + Number.EPSILON) * 100) / 100;
    }

    return {
      totaalExcl: round(ret.totaalExcl),
      totaalIncl: round(ret.totaalIncl),
      btw: {
        0: round(ret.btw[0]),
        9: round(ret.btw[9]),
        21: round(ret.btw[21]),
      },
    };
  }

  public uploadBon(files: FileList): void {
    const formData = new FormData();

    if (files.length !== 1) {
      return;
    }

    this.uploading = true;
    formData.append('bon', files[0], files[0].name);

    axios({
      method: 'post',
      url: '/declaratie/upload',
      data: formData,
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then((res) => {
      this.uploading = false;
      this.bonUploaden = false;
      this.nieuweBon(res.data.file, res.data.id);
    }).catch((err) => {
      this.uploading = false;
      alert(err);
    });
  }

  public declaratieOpslaan(indienen: boolean): void {
    this.declaratie.status = indienen ? 'ingediend' : 'concept';
    this.submitting = true;
    this.errors = [];

    axios.request<DeclaratieOpslaanData, DeclaratieOpslaanResponse>({
      method: 'post',
      url: '/declaratie/opslaan',
      data: {
        declaratie: this.declaratie,
      },
    }).then((res) => {
      const {data} = res;
      if (data.id) {
        this.declaratie.id = data.id;
        this.declaratie.status = data.status;
        this.declaratie.statusDatums = data.statusDatums;
      }
      this.errors = data.messages;
      this.submitting = false;
      if (data.success) {
        window.scrollTo(0, 0);
      }
    }).catch((err) => {
      this.submitting = false;
      alert(err);
    });
  }
}
</script>

<style scoped lang="scss">
.declaratie {
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  font-size: 1rem;
}

.voortgang {
  border-radius: 10px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: stretch;
  overflow: hidden;
  gap: 3px;
  margin: 10px 0 30px;

  @media screen and (max-width: 760px) {
    flex-direction: column;
  }

  .fase {
    color: #D0D0D0;
    background: #F6F6F6;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    padding: 8px;

    .status {
      font-size: 16px;
      font-weight: 400;
    }

    .datum {
      font-size: 13px;
      font-weight: 300;
    }

    &.done {
      color: #393939;
    }

    &.active {
      color: white;

      &.concept {
        background: #8e8e8e;
      }

      &.ingediend {
        background: #E19600;
      }

      &.goedgekeurd {
        background: #00DB49;
      }

      &.afgekeurd {
        background: #E20000;
      }

      &.uitbetaald {
        background: #2C3E50;
      }
    }
  }
}

.field {
  & > label {
    display: block;
    color: #3C3C3C;
    font-weight: 600;
    margin-bottom: 4px;
  }

  input[type=text],
  input[type=date],
  input[type=tel],
  select,
  textarea {
    border: 1px solid #868686;
    outline: none;
    padding: 0.4rem;
    font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;
    font-size: 1.2rem;
    font-weight: 300;
    border-radius: 4px;
    display: block;
    width: 100%;
  }

  [type=radio] + label {
    font-weight: 300;
    font-size: 1.2rem;
    margin-left: 6px;
  }

  & + .field {
    margin-top: 11px;
  }
}

.bonnen {
  border-radius: 6px;
  border: 1px solid #D0D0D0;
  margin: 30px 0;

  &.bonnen-weergave {
    display: grid;
    grid-template-columns: 550px auto;
    height: 400px;
    overflow: hidden;

    @media screen and (max-width: 768px) {
      grid-template-columns: auto;
      grid-template-rows: 400px 400px;
      height: auto;
    }

    .lijst {
      height: 100%;
      overflow-y: auto;
      background: #F2F2F2;

      .nieuwe-bon {
        text-align: center;
        font-size: 21px;
        padding: 14px 0 33px;
        color: #2ECC71;
        cursor: pointer;
      }

      .bon {
        border-bottom: 1px solid #D0D0D0;

        .bon-collapsed {
          padding: 12px 25px;
          background: #FAFAFA;
          cursor: pointer;
          display: flex;
          justify-content: space-between;

          .title {
            font-size: 16px;
            color: #4A4A4A;
            margin-bottom: 0;
          }

          .date {
            font-weight: 300;
            font-size: 15px;
          }

          .right {
            .btw {
              margin-top: -3px;
              font-size: 13px;
              font-weight: 300;
              text-align: right;
            }
          }
        }

        .bon-selected {
          padding: 21px 25px;
          background: white;
        }

        .bon-regels {
          margin-top: 11px;
        }

        .regels-row {
          display: grid;
          grid-template-columns: 3fr 1fr 1fr 15px;
          grid-column-gap: 6px;
          margin-top: 9px;

          .field + .field {
            margin-top: 0;
          }

          .trash, .add {
            line-height: 33px;
            text-align: right;
            cursor: pointer;

            &.trash {
              color: #a5a5a5;

              &:hover {
                color: #676767;
              }
            }

            &.add {
              color: #2ECC71;
            }
          }

          &.nieuw {
            cursor: pointer;

            .field {
              position: relative;

              &:before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                background: rgba(255, 255, 255, 0.65);
              }
            }

            input:disabled, select:disabled {
              background: white;
            }
          }

          &.totaal {
            font-size: 15px;
            font-weight: 300;

            .onderdeel {
              text-align: right;
              padding-right: 20px;
            }

            &.streep {
              div {
                padding-top: 9px;
              }

              .bedrag {
                border-top: 1px solid #C7C7C7;
              }
            }

            .bedrag {
              position: relative;
              text-align: right;

              &:before {
                content: '€';
                position: absolute;
                left: 0;
              }
            }

            &.totaalBold {
              font-weight: 600;
            }
          }
        }

        .bonVerwijderen {
          float: right;
          color: #a5a5a5;
          cursor: pointer;
          margin-top: 4px;

          &:hover {
            color: #676767;
          }
        }

        .title {
          font-size: 18px;
          font-weight: 600;
          margin-bottom: 5px;
        }

        label {
          font-size: 11px;
        }

        input {
          font-size: 1.1rem;
        }
      }
    }

    .voorbeeld {
      background: #545454;

      iframe {
        width: 100%;
        height: 100%;
        border: none;
      }
    }
  }

  &.bon-upload {
    background: linear-gradient(135deg, #ffffff, #f4f6f9 41%, #eff3f6);
    position: relative;
    overflow: hidden;
    min-height: 400px;
    padding: 110px;

    @media screen and (max-width: 768px) {
      padding: 50px;
    }

    .inhoud {
      position: relative;
    }

    .titel {
      font-size: 2rem;
      font-weight: 600;
    }

    p {
      font-size: 1.5rem;
      font-weight: 300;
      max-width: 360px;
      line-height: 1.4;
      margin-top: 9px;
      margin-bottom: 14px;
    }

    .buttons {
      button, label {
        width: 110px;
        border-radius: 3px;
        -webkit-appearance: none;
        padding: 6px 0;
        border: none;
        margin-right: 10px;
        margin-top: 5px;
        text-align: center;
        cursor: pointer;

        &.loading {
          cursor: default;
        }

        &.blue {
          background: #00087B;
          color: white;
          font-weight: 600;

          &:hover:not(.loading) {
            background: #3498db;
          }
        }

        &.open {
          border: 1px solid #D0D0D0;
          color: #898989;
          font-weight: 600;

          &:hover {
            border: 1px solid #898989;
            color: black;
          }
        }
      }

      input {
        display: none;
      }
    }

    &:before {
      content: '';
      position: absolute;
      background: url("../../../images/declaratie.svg") right bottom no-repeat;
      background-size: auto 210px;
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
    }
  }
}

.totaal {
  display: grid;
  grid-template-columns: 1fr 1fr;

  .left {
    font-size: 21px;
    font-weight: 600;
  }

  .right {
    text-align: right;

    .title {
      font-size: 27px;
      font-weight: 600;
      color: black;
      margin-bottom: 0;
    }

    .btw {
      margin-top: -6px;
      font-size: 16px;
      font-weight: 300;
    }
  }
}

.save-buttons {
  margin-top: 30px;
  text-align: right;

  button {
    border-radius: 3px;
    -webkit-appearance: none;
    padding: 7px 21px;
    border: none;
    margin-left: 10px;
    text-align: center;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;

    @media screen and (max-width: 576px) {
      width: 100%;
      margin: 10px 0 0 0;
    }

    &.concept {
      border: 1px solid #D0D0D0;
      color: #6a6a6a;
      font-weight: 400;

      &:hover {
        color: #4e4e4e;
      }
    }

    &.confirm {
      color: white;
      background: #2ECC71;

      &:hover {
        background: #48E088;
      }
    }
  }
}
</style>
