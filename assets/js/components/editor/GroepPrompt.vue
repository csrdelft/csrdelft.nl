<template>
  <div>
    <div class="modal-backdrop" />
    <div class="modal" style="display: block" tabindex="-1" @click="sluiten">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Invoegen: {{ type }}</h5>
            <button
              type="button"
              class="close"
              aria-label="Sluiten"
              @click="close()"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="mb-3 row">
              <label class="col-sm-2 col-form-label" for="zoeken">Groep</label>
              <div class="col-sm-10">
                <input
                  id="zoeken"
                  type="text"
                  class="form-control"
                  :class="{ loading }"
                  @input="ingevuld = $event.target.value"
                  @keyup="update"
                />
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-sm-2 col-form-label">Status</label>
              <div class="col-sm-10">
                <div class="form-check form-check-inline">
                  <input
                    id="status-ht"
                    v-model="zoekHt"
                    type="checkbox"
                    class="form-check-input"
                    :disabled="!zoekFt && !zoekOt"
                    @change="update"
                  />
                  <label class="form-check-label" for="status-ht">h.t.</label>
                </div>
                <div class="form-check form-check-inline">
                  <input
                    id="status-ft"
                    v-model="zoekFt"
                    type="checkbox"
                    class="form-check-input"
                    :disabled="!zoekHt && !zoekOt"
                    @change="update"
                  />
                  <label class="form-check-label" for="status-ft">f.t.</label>
                </div>
                <div class="form-check form-check-inline">
                  <input
                    id="status-ot"
                    v-model="zoekOt"
                    type="checkbox"
                    class="form-check-input"
                    :disabled="!zoekHt && !zoekFt"
                    @change="update"
                  />
                  <label class="form-check-label" for="status-ot">o.t.</label>
                </div>
              </div>
            </div>
            <div class="list-group">
              <a
                v-for="groep of groepen"
                :key="groep.id"
                class="list-group-item list-group-item-action"
                href="#"
                @click.prevent="selectgroep(groep.naam, groep.id)"
              >
                {{ groep.naam }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import axios, { CancelTokenSource } from 'axios';

interface GroepZoekResponse {
  url: string;
  label: string;
  value: string;
  naam: string;
  icon: string;
  id: number;
}

export default defineComponent({
  props: {
    type: {
      required: true,
      type: String,
    },
    selectgroep: {
      required: true,
      type: Function as PropType<(naam: string, id: number) => void>,
    },
    close: {
      required: true,
      type: Function as PropType<() => void>,
    },
  },
  data: () => ({
    groepen: [] as GroepZoekResponse[],
    ingevuld: '',
    zoekHt: true,
    zoekFt: true,
    zoekOt: false,
    loading: false,
    source: null as CancelTokenSource,
  }),
  created() {
    this.update();
  },
  methods: {
    update(): void {
      this.loading = true;
      // Cancel vorige request en maak een nieuwe cancel source
      this.source?.cancel('Stop zoeken');
      this.source = axios.CancelToken.source();

      const status = [];
      if (this.zoekHt) status.push('ht');
      if (this.zoekFt) status.push('ft');
      if (this.zoekOt) status.push('ot');

      const zoekStatus = status.join(',');

      axios
        .get<GroepZoekResponse[]>(
          `/groepen/${this.type}/zoeken?status=${zoekStatus}&q=${this.ingevuld}`,
          { cancelToken: this.source.token }
        )
        .then((groepen) => {
          this.groepen = groepen.data;
          // Loading wordt alleen weer false als er daadwerkelijk een request eindigt
          this.loading = false;
        })
        .catch(() => {
          /* Maakt niet uit, voorkom log naar console */
        });
    },
    sluiten(e: MouseEvent): void {
      if (e.target instanceof Element && e.target.className == 'modal') {
        this.close();
      }
    },
  },
});
</script>

<style scoped></style>
