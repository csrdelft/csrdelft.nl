import axios from 'axios'
import Dropzone from "dropzone";
import {selectAll} from "./dom";
import {html} from "./util";

interface Afbeelding {
	name: string
	size: number
	type: string
	thumbnail?: string
}

export const initDropzone = (el: HTMLFormElement): void => {
	const {naam, accept, deleteUrl, maxsize, coverUrl, existingUrl} = el.dataset
	const thisDropzone = new Dropzone(el, {
		paramName: naam,
		url: el.action,
		acceptedFiles: accept,
		addRemoveLinks: true,
		removedfile: async function (file) {
			try {
				await axios.post(deleteUrl, "foto=" + file.name)

				file.previewElement.remove()
			} catch (e) {
				throw new Error(e)
			}
		},
		maxFilesize: Number(maxsize),
		maxFiles: 500,
		dictDefaultMessage: "Sleep hier bestanden om te uploaden",
		dictFallbackMessage: "Je browser ondersteund niet het slepen van bestanden.",
		dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
		dictFileTooBig: "Te groot bestand: ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.",
		dictInvalidFileType: "Bestanden van dit type zijn niet toegestaan.",
		dictResponseError: "Server responded with {{statusCode}} code.",
		dictCancelUpload: "Annuleren",
		dictCancelUploadConfirmation: "Toevoegen annuleren. Weet u het zeker?",
		dictRemoveFile: "X",
		dictRemoveFileConfirmation: "Bestand verwijderen. Weet u het zeker?",
		dictMaxFilesExceeded: "You can not upload any more files.",
		init: function () {
			this.on('addedfile', function (file) {
				const coverBtn = Dropzone.createElement('<a class="btn" title="Stel deze foto in als omslag voor het album">Omslag</a>');
				file.previewElement.appendChild(coverBtn);

				coverBtn.addEventListener('click', async function (e) {
					// Make sure the button click doesn't submit the form
					e.preventDefault();
					e.stopPropagation();

					try {
						await axios.post(coverUrl, 'foto=' + file.name)

						coverBtn.replaceWith(html`<span><span class="fa fa-check"></span> Omslag</span>`)
					} catch (e) {
						throw new Error(e)
					}
				});

				selectAll('.dz-remove').forEach(el => el.className = 'btn')
			});
		}
	});

	const showExisting = html`<a href="#"><span class="ico photos"></span> Toon bestaande foto's in dit album</a>`;
	el.appendChild(showExisting)
	showExisting.addEventListener('click', async function (e) {
		e.preventDefault()

		showExisting.remove()

		const response = await axios.post(existingUrl)
		for (const value of Object.values<Afbeelding>(response.data)) {
			const mockFile = {name: value.name, size: value.size, type: value.type};
			thisDropzone.emit('addedfile', mockFile);
			if (typeof value.thumbnail !== 'undefined') {
				thisDropzone.emit('thumbnail', mockFile, value.thumbnail);
			}
			thisDropzone.emit('complete', mockFile);
		}
	})

}
