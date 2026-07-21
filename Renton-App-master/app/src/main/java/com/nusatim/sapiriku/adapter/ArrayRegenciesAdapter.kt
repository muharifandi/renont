package com.nusatim.sapiriku.adapter

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.TextView
import com.nusatim.sapiriku.domain.model.Regencies

class ArrayRegenciesAdapter(
    context: Context,
    textViewResourceId: Int,
    private val regencies: ArrayList<Regencies>
) : ArrayAdapter<Regencies>(context, textViewResourceId, regencies) {

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val view = convertView
            ?: LayoutInflater.from(context).inflate(android.R.layout.simple_list_item_1, parent, false)

        view.findViewById<TextView>(android.R.id.text1).text = regencies[position].name

        return view
    }

    override fun getItem(index: Int): Regencies = regencies[index]
}
