package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.addvehicle
import android.content.Context
import android.net.Uri
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.core.view.isVisible
import androidx.lifecycle.LifecycleCoroutineScope
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.databinding.ItemImageForUploadBinding
import com.nusatim.sapiriku.domain.model.UploadImageResult
import com.nusatim.sapiriku.domain.model.VehicleItemImage
import kotlinx.coroutines.launch

/**
 * Owns the "add / retry / remove vehicle photo" gallery UI and its upload/delete side effects,
 * so [PartnerAddVehicleActivity] doesn't have to.
 */
class VehiclePhotoManager(
    private val context: Context,
    private val lifecycleScope: LifecycleCoroutineScope,
    private val container: ViewGroup,
    private val preview: ImageView,
    private val uploadPhoto: suspend (String) -> Result<UploadImageResult>,
    private val deletePhoto: suspend (Int) -> Result<Unit>
) {
    data class PhotoEntry(
        var image: VehicleItemImage,
        val localUri: Uri? = null,
        var isUploading: Boolean = false,
        var uploadFailed: Boolean = false
    )

    private val photos = mutableListOf<PhotoEntry>()

    fun bindExisting(existing: List<VehicleItemImage>) {
        existing.forEach { photos.add(PhotoEntry(image = it)) }
        render()
    }

    fun add(uri: Uri) {
        val entry = PhotoEntry(image = VehicleItemImage(id = 0, itemId = 0, img = uri.toString()), localUri = uri, isUploading = true)
        photos.add(entry)
        render()
        upload(entry, uri)
    }

    fun newPhotoPaths(): List<String> = photos.filter { it.image.id == 0 }.map { it.image.img.orEmpty() }

    private fun retry(entry: PhotoEntry) {
        val uri = entry.localUri ?: return
        entry.isUploading = true
        entry.uploadFailed = false
        render()
        upload(entry, uri)
    }

    private fun upload(entry: PhotoEntry, uri: Uri) {
        lifecycleScope.launch {
            val result = uploadPhoto(uri.toString())
            entry.isUploading = false
            result.onSuccess { response ->
                entry.image = entry.image.copy(img = response.fileName.orEmpty())
                entry.uploadFailed = false
            }.onFailure {
                entry.uploadFailed = true
            }
            render()
        }
    }

    private fun remove(entry: PhotoEntry) {
        AlertDialog.Builder(context)
            .setTitle(context.getString(R.string.photo))
            .setMessage(context.getString(R.string.delete_confirm))
            .setNegativeButton(android.R.string.cancel, null)
            .setPositiveButton(R.string.yes) { _, _ ->
                if (entry.image.id != 0) {
                    lifecycleScope.launch {
                        val result = deletePhoto(entry.image.id)
                        if (result.isSuccess) {
                            photos.remove(entry)
                            render()
                        } else {
                            Toast.makeText(context, context.getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                } else {
                    photos.remove(entry)
                    render()
                }
            }
            .show()
    }

    private fun render() {
        container.removeAllViews()
        photos.forEach { entry -> container.addView(buildView(entry)) }

        val last = photos.lastOrNull()
        if (last != null) {
            loadInto(preview, last)
        } else {
            preview.setImageDrawable(null)
        }
    }

    private fun loadInto(imageView: ImageView, entry: PhotoEntry) {
        val model: Any = entry.localUri ?: if (entry.image.id != 0) Config.BASE_VEHICLE_IMAGE + entry.image.img else entry.image.img.orEmpty()
        imageView.load(model) {
            placeholder(R.drawable.ic_time)
            error(R.drawable.ic_broken_image)
        }
    }

    private fun buildView(entry: PhotoEntry): View {
        val itemBinding = ItemImageForUploadBinding.inflate(LayoutInflater.from(context), container, false)

        loadInto(itemBinding.imgForUpload, entry)
        itemBinding.imgForUpload.setOnClickListener { loadInto(preview, entry) }
        itemBinding.imgCancel.setOnClickListener { remove(entry) }
        itemBinding.btnTryUploadImage.setOnClickListener { retry(entry) }

        itemBinding.progressUpload.isVisible = entry.isUploading
        itemBinding.btnTryUploadImage.isVisible = entry.uploadFailed
        itemBinding.imgCancel.isVisible = !entry.isUploading
        itemBinding.imgForUpload.alpha = if (entry.isUploading) 0.2f else 1.0f

        return itemBinding.root
    }
}
