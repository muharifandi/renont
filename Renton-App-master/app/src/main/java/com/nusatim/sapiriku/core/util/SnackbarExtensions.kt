package com.nusatim.sapiriku.core.util

import android.view.Gravity
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.FrameLayout
import androidx.coordinatorlayout.widget.CoordinatorLayout
import androidx.core.content.ContextCompat
import com.google.android.material.snackbar.Snackbar
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.ViewSapirikuSnackbarBinding

enum class SnackbarType {
    SUCCESS, ERROR, WARNING, PENDING
}

enum class SnackbarPosition {
    TOP, CENTER, BOTTOM
}

/**
 * Extension untuk menampilkan Snackbar kustom dengan pilihan posisi.
 * @param message Pesan yang ingin ditampilkan.
 * @param type Jenis snackbar (warna/icon).
 * @param position Posisi kemunculan (TOP, CENTER, BOTTOM).
 * @param anchorViewId Optional ID view untuk anchor (misal R.id.toolbar) jika posisi TOP.
 */
fun View.showSapirikuSnackbar(
    message: String,
    type: SnackbarType,
    position: SnackbarPosition = SnackbarPosition.BOTTOM,
    anchorViewId: Int? = null
) {
    val snackbar = Snackbar.make(this, "", Snackbar.LENGTH_LONG)
    val snackbarView = snackbar.view as ViewGroup
    
    // Matikan styling default snackbar
    snackbarView.setBackgroundColor(android.graphics.Color.TRANSPARENT)
    snackbarView.setPadding(0, 0, 0, 0)

    // Setup Layout Params berdasarkan posisi
    val params = snackbarView.layoutParams
    if (params is FrameLayout.LayoutParams) {
        params.gravity = when (position) {
            SnackbarPosition.TOP -> Gravity.TOP
            SnackbarPosition.CENTER -> Gravity.CENTER
            SnackbarPosition.BOTTOM -> Gravity.BOTTOM
        }
        
        // Jika di TOP dan ada anchor, beri margin agar tidak menutupi toolbar
        if (position == SnackbarPosition.TOP) {
            val anchorView = (this.context as? android.app.Activity)?.findViewById<View>(anchorViewId ?: R.id.toolbar)
            if (anchorView != null) {
                params.topMargin = anchorView.height + 16 // Tambah sedikit gap
            } else {
                params.topMargin = 100 // Fallback gap jika toolbar tidak ketemu
            }
        }
        
        snackbarView.layoutParams = params
    } else if (params is CoordinatorLayout.LayoutParams) {
        params.gravity = when (position) {
            SnackbarPosition.TOP -> Gravity.TOP
            SnackbarPosition.CENTER -> Gravity.CENTER
            SnackbarPosition.BOTTOM -> Gravity.BOTTOM
        }
        
        if (position == SnackbarPosition.TOP) {
            val anchorView = (this.context as? android.app.Activity)?.findViewById<View>(anchorViewId ?: R.id.toolbar)
            if (anchorView != null) {
                params.topMargin = anchorView.height + 16
            }
        }
        
        snackbarView.layoutParams = params
    }

    val binding = ViewSapirikuSnackbarBinding.inflate(LayoutInflater.from(context))
    binding.txtMessage.text = message

    val (color, icon) = when (type) {
        SnackbarType.SUCCESS -> R.color.green to R.drawable.ic_notifications
        SnackbarType.ERROR -> R.color.red to R.drawable.ic_notifications
        SnackbarType.WARNING -> R.color.orange to R.drawable.ic_notifications
        SnackbarType.PENDING -> R.color.cokmud to R.drawable.ic_time
    }

    binding.cardContainer.setCardBackgroundColor(ContextCompat.getColor(context, color))
    binding.imgIcon.setImageResource(icon)

    snackbarView.removeAllViews() // Bersihkan view bawaan
    snackbarView.addView(binding.root)
    snackbar.show()
}
