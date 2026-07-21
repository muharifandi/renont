package com.rentone.user.presentation.feature.account.adapter

import android.content.Context
import android.graphics.BitmapFactory
import android.util.Base64
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import com.rentone.user.databinding.ItemCustomerBankBinding
import com.rentone.user.domain.model.CustomerBank

class ListCustomerBankAdapter(
    private val ctx: Context,
    list: ArrayList<CustomerBank>
) : ArrayAdapter<CustomerBank>(ctx, 0, list) {

    private val dataList: List<CustomerBank> = list

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val binding = if (convertView == null) {
            ItemCustomerBankBinding.inflate(LayoutInflater.from(ctx), parent, false)
        } else {
            ItemCustomerBankBinding.bind(convertView)
        }

        val data = dataList[position]

        binding.icon.setImageBitmap(null)
        data.icon?.let {
            val decodedString = Base64.decode(it, Base64.DEFAULT)
            val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
            binding.icon.setImageBitmap(decodedByte)
        }
        binding.txtBankName.text = data.bankName
        binding.txtName.text = data.name
        binding.txtDetail.text = data.bankNumber

        return binding.root
    }
}
