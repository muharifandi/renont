package com.rentone.user.presentation.feature.partner.rentvehicle.listpromote.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ItemPartnerListPromoteVehicleBinding
import com.rentone.user.domain.model.PromoteVehicle
import java.text.NumberFormat
import java.util.Locale

class PartnerListPromoteVehicleAdapter(
    private val onCancelPromoteClick: (PromoteVehicle) -> Unit
) : LoadingFooterListAdapter<PromoteVehicle>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        VehicleViewHolder(ItemPartnerListPromoteVehicleBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: PromoteVehicle, position: Int) {
        (holder as VehicleViewHolder).bind(item)
    }

    inner class VehicleViewHolder(
        private val binding: ItemPartnerListPromoteVehicleBinding
    ) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: PromoteVehicle) {
            val ctx = binding.root.context

            if (item.img != null) {
                binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + item.img) {
                    error(R.drawable.ic_broken_image)
                }
            } else {
                binding.previewVehicle.setImageResource(R.drawable.no_image)
            }

            binding.txtViewer.text = item.viewer.toString()
            binding.txtTitle.text = item.title
            binding.txtStatus.text = item.statusName
            binding.txtDateRange.text =
                "${ViewUtils.mysqlDateToNormalDate(item.startDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
                    ViewUtils.mysqlDateToNormalDate(item.endDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")
            binding.txtDays.text = "${item.days} ${ctx.getString(R.string.days)}"
            binding.txtPricePerDay.text = "Rp. ${ViewUtils.formatCurrency(item.pricePerDay)},- ${ctx.getString(R.string.per_day)}"
            binding.txtTotalPayment.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.totalPayment)},-"

            if (item.status == 0 || item.status == 1) {
                binding.itemContainer.setOnClickListener { onCancelPromoteClick(item) }
            } else {
                binding.itemContainer.setOnClickListener(null)
            }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<PromoteVehicle>() {
            override fun areItemsTheSame(oldItem: PromoteVehicle, newItem: PromoteVehicle) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: PromoteVehicle, newItem: PromoteVehicle) = oldItem == newItem
        }
    }
}
