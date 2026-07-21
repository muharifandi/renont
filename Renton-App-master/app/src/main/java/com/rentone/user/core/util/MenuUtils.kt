package com.rentone.user.core.util

import android.app.Activity
import android.content.Context
import android.content.DialogInterface
import android.content.Intent
import android.net.Uri
import android.provider.Settings
import android.widget.ArrayAdapter
import androidx.appcompat.app.AlertDialog
import com.rentone.user.R

object MenuUtils {

    fun buildPopupList(
        context: Context,
        title: String,
        icon: Int,
        arrayAdapter: ArrayAdapter<*>,
        onItemClick: DialogInterface.OnClickListener,
        onClear: DialogInterface.OnClickListener? = null
    ) {
        AlertDialog.Builder(context).apply {
            setIcon(icon)
            setTitle(title)
            setNegativeButton(R.string.cancel) { dialog, _ -> dialog.dismiss() }
            onClear?.let { setPositiveButton(R.string.clear, it) }
            setAdapter(arrayAdapter, onItemClick)
            show()
        }
    }

    fun openPermissionSettings(activity: Activity) {
        val intent = Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS).apply {
            data = Uri.parse("package:${activity.packageName}")
            addCategory(Intent.CATEGORY_DEFAULT)
            flags = Intent.FLAG_ACTIVITY_NEW_TASK
        }
        activity.startActivity(intent)
    }
}
