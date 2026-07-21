package com.nusatim.sapiriku.presentation.feature.account.requesttopup.adapter
import android.content.Context
import android.graphics.BitmapFactory
import android.util.Base64
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import com.nusatim.sapiriku.databinding.ItemCustomerBankBinding
import com.nusatim.sapiriku.domain.model.CompanyBank

class ListCompanyBankAdapter(
    private val ctx: Context,
    list: ArrayList<CompanyBank>
) : ArrayAdapter<CompanyBank>(ctx, 0, list) {

    private val dataList: List<CompanyBank> = list

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
