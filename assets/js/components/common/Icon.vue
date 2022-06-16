<template>
  <i :class="iconClass" aria-hidden="true"></i>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';

@Component({
  name: 'Icon',
})
export default class Icon extends Vue {
  @Prop()
  icon: string;

  private alias = {
    // algemeen
    toevoegen: 'plus',
    bewerken: 'pencil',
    verwijderen: 'trash',
    goedkeuren: 'circle-check',
    verjaardag: 'cake-candles',
    vraagteken: 'circle-question',
    tonen: 'eye',
    verbergen: 'eye-slash',
    sluiten: 'xmark',
    kopieren: 'clipboard',
    undo: 'arrow-rotate-left',
    redo: 'arrow-rotate-right',
    alert: 'stop',
    stop: 'ban',
    //documumenten
    'mime-onbekend': 'file-circle-exclamation',
    'mime-audio': 'file-audio',
    'mime-html': 'file-code',
    'mime-word': 'file-word',
    'mime-excel': 'file-excel',
    'mime-powerpoint': 'file-powerpoint',
    'mime-image': 'file-image',
    'mime-pdf': 'file-pdf',
    'mime-plain': 'file-lines',
    'mime-zip': 'file-zipper',
    // forum
    citeren: 'quote-left',
    slotje: 'lock',
    plakkerig: 'note-sticky',
    belangrijk: 'star',
    ruby: 'gem',
    asterisk_orange: 'star',
    flag_red: 'flag', // Backwards compatibility
    flag_orange: 'flag',
    flag_green: 'flag',
    flag_blue: 'flag',
    // profiel
    stats: 'chart-line',
    resetpassword: 'user-lock',
    instellingen: 'gear',
    // melding
    'alert-danger': 'bell-exclamation',
    'alert-info': 'bell-on',
    'alert-success': 'circle-check',
    'alert-warning': 'bell',
    // overig
    tabel: 'table',
    log: 'rectangle-terminal',
    prullenbak: 'dumpster',

    calendar: 'calendar',
    forum: 'comments',
    profiel: 'user',
    fotoalbum: 'images',
    document: 'file',
    woonoord: 'home',
    commissie: 'users',
    ondervereniging: 'users',
    kring: 'circle-notch',
    boek: 'book',
    wiki: 'book-atlas',
    rechten: 'key',
  };

  private get iconClass() {
    return `fas fa-${this.icon}`;
  }
}
</script>

<style scoped></style>
