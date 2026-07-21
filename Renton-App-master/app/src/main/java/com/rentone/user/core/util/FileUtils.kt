package com.rentone.user.core.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.net.Uri
import android.webkit.MimeTypeMap
import androidx.core.content.FileProvider
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File
import java.io.FileOutputStream

object FileUtils {

    private const val MAX_WIDTH = 1280f
    private const val MAX_HEIGHT = 1280f

    fun createPartFromString(param: String): RequestBody {
        return param.toRequestBody("multipart/form-data".toMediaTypeOrNull())
    }

    fun prepareFileImagePart(context: Context, name: String, path: String): MultipartBody.Part {
        // Simple file prep for now, assuming compression is handled elsewhere or later
        val file = File(path)
        val uri = FileProvider.getUriForFile(context, "${context.packageName}.fileprovider", file)
        val mimeType = context.contentResolver.getType(uri) ?: getMimeType(path) ?: "image/*"
        val requestFile = file.asRequestBody(mimeType.toMediaTypeOrNull())
        return MultipartBody.Part.createFormData(name, file.name, requestFile)
    }

    /** For content:// URIs returned by ActivityResultContracts photo/document pickers. */
    fun prepareFileImagePart(context: Context, name: String, uri: Uri): MultipartBody.Part {
        val mimeType = context.contentResolver.getType(uri) ?: "image/*"
        val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() } ?: ByteArray(0)
        val requestFile = bytes.toRequestBody(mimeType.toMediaTypeOrNull())
        val fileName = queryDisplayName(context, uri) ?: "image_${System.currentTimeMillis()}.jpg"
        return MultipartBody.Part.createFormData(name, fileName, requestFile)
    }

    private fun queryDisplayName(context: Context, uri: Uri): String? {
        return context.contentResolver.query(uri, arrayOf(android.provider.OpenableColumns.DISPLAY_NAME), null, null, null)?.use { cursor ->
            val nameIndex = cursor.getColumnIndex(android.provider.OpenableColumns.DISPLAY_NAME)
            if (cursor.moveToFirst() && nameIndex >= 0) cursor.getString(nameIndex) else null
        }
    }

    fun getMimeType(url: String): String? {
        val extension = MimeTypeMap.getFileExtensionFromUrl(url)
        return MimeTypeMap.getSingleton().getMimeTypeFromExtension(extension)
    }

    /**
     * Splits a step-by-step wizard's aggregated [values] into multipart form fields, treating
     * every key in [imageKeys] as a `content://` URI (as produced by [prepareFileImagePart]) and
     * everything else as a plain string field.
     */
    fun buildMultipartForm(
        context: Context,
        values: Map<String, String>,
        imageKeys: Set<String>
    ): Pair<Map<String, RequestBody>, List<MultipartBody.Part>> {
        val form = mutableMapOf<String, RequestBody>()
        val files = mutableListOf<MultipartBody.Part>()

        values.forEach { (key, value) ->
            if (key in imageKeys) {
                if (value.isNotEmpty()) {
                    files.add(prepareFileImagePart(context, key, Uri.parse(value)))
                }
            } else {
                form[key] = createPartFromString(value)
            }
        }

        return form to files
    }

    /**
     * Downscales the image at [imagePath] to fit within [MAX_WIDTH]x[MAX_HEIGHT] and writes it
     * as a JPEG into [context]'s cache directory. Returns the path of the compressed file.
     */
    suspend fun compressImage(context: Context, imagePath: String): String? = withContext(Dispatchers.IO) {
        val file = File(imagePath)
        val options = BitmapFactory.Options().apply { inJustDecodeBounds = true }
        BitmapFactory.decodeFile(imagePath, options)

        var actualWidth = options.outWidth
        var actualHeight = options.outHeight
        val imgRatio = actualWidth.toFloat() / actualHeight.toFloat()
        val maxRatio = MAX_WIDTH / MAX_HEIGHT

        if (actualHeight > MAX_HEIGHT || actualWidth > MAX_WIDTH) {
            when {
                imgRatio < maxRatio -> {
                    val ratio = MAX_HEIGHT / actualHeight
                    actualWidth = (ratio * actualWidth).toInt()
                    actualHeight = MAX_HEIGHT.toInt()
                }
                imgRatio > maxRatio -> {
                    val ratio = MAX_WIDTH / actualWidth
                    actualHeight = (ratio * actualHeight).toInt()
                    actualWidth = MAX_WIDTH.toInt()
                }
                else -> {
                    actualHeight = MAX_HEIGHT.toInt()
                    actualWidth = MAX_WIDTH.toInt()
                }
            }
        }

        val bitmap = runCatching { BitmapFactory.decodeFile(imagePath) }.getOrNull() ?: return@withContext null
        val scaledBitmap = Bitmap.createScaledBitmap(bitmap, actualWidth, actualHeight, false)

        val outputDir = File(context.cacheDir, "compressed").apply { if (!exists()) mkdirs() }
        val outputFile = File(outputDir, file.name)

        FileOutputStream(outputFile).use { out ->
            scaledBitmap.compress(Bitmap.CompressFormat.JPEG, 80, out)
        }

        outputFile.absolutePath
    }
}
