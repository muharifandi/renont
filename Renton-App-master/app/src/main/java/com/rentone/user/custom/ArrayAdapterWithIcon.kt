package com.rentone.user.custom

import android.content.Context
import android.graphics.BitmapFactory
import android.util.Base64
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.ImageView
import android.widget.TextView
import androidx.core.content.ContextCompat
import com.rentone.user.R
import com.rentone.user.domain.model.BasicData

class ArrayAdapterWithIcon(
    private val ctx: Context,
    list: ArrayList<BasicData>
) : ArrayAdapter<BasicData>(ctx, 0, list) {

    private val dataList: List<BasicData> = list

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val view = convertView
            ?: LayoutInflater.from(ctx).inflate(R.layout.item_custom_adapter_with_icon, parent, false)

        val data = dataList[position]

        val icon = view.findViewById<ImageView>(R.id.icon)
        icon.setImageBitmap(null)
        data.icon?.let {
            val decodedString = Base64.decode(it, Base64.DEFAULT)
            val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
            icon.setImageBitmap(decodedByte)
            icon.setColorFilter(ContextCompat.getColor(ctx, R.color.colorAccent))
        }
        view.findViewById<TextView>(R.id.name).text = data.name

        return view
    }
}
