package com.nusatim.sapiriku.presentation.feature.customer.transaction.rentvehicle.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.ui.LoadingFooterListAdapter
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.databinding.ItemListRentVehicleTransactionBinding
import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import java.text.NumberFormat
import java.util.Locale

class ListCustomerRentVehicleTransactionAdapter(
    private val onItemClick: (RentVehicleTransaction) -> Unit
) : LoadingFooterListAdapter<RentVehicleTransaction>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        TransactionViewHolder(ItemListRentVehicleTransactionBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: RentVehicleTransaction, position: Int) {
        (holder as TransactionViewHolder).bind(item)
    }

    inner class TransactionViewHolder(
        private val binding: ItemListRentVehicleTransactionBinding
    ) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: RentVehicleTransaction) {
            val ctx = binding.root.context
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.dateModified.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtTitle.text = item.vehicleTitle
            binding.txtStatus.text = item.statusName

            if (item.img != null) {
                binding.imagePreview.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + item.img) {
                    error(R.drawable.ic_broken_image)
                }
            } else {
                binding.imagePreview.setImageResource(R.drawable.no_image)
            }

            val days = ViewUtils.getCountOfDays(
                ViewUtils.mysqlDateToNormalDate(item.startDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "yyyy-MM-dd"),
                ViewUtils.mysqlDateToNormalDate(item.endDate.orEmpty(), "yyyy-MM-dd HH:mm:ss", "yyyy-MM-dd")
            )
            binding.txtPricePackageName.text = "${item.pricePackageName} @$days ${ctx.getString(R.string.days)}"
            binding.txtTotalPayment.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.totalPayment)},-"

            binding.itemContainer.setOnClickListener { onItemClick(item) }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<RentVehicleTransaction>() {
            override fun areItemsTheSame(oldItem: RentVehicleTransaction, newItem: RentVehicleTransaction) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: RentVehicleTransaction, newItem: RentVehicleTransaction) = oldItem == newItem
        }
    }
}
